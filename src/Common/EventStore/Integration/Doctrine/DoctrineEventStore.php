<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\Integration\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\TransactionIsolationLevel;
use Doctrine\DBAL\Types\Types;
use Gaming\Common\EventStore\CleanableEventStore;
use Gaming\Common\EventStore\ContentSerializer;
use Gaming\Common\EventStore\DomainEvent;
use Gaming\Common\EventStore\EventStore;
use Gaming\Common\EventStore\Exception\DuplicateVersionInStreamException;
use Gaming\Common\EventStore\Exception\EventStoreException;
use Gaming\Common\EventStore\GapDetection;
use Gaming\Common\EventStore\PollableEventStore;
use Gaming\Common\EventStore\StoredEvent;
use Gaming\Common\EventStore\StoredEventFilters;
use Throwable;

final class DoctrineEventStore implements EventStore, PollableEventStore, CleanableEventStore, GapDetection
{
    /**
     * @param ContentSerializer $contentSerializer Must serialize into and deserialize from valid JSON.
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly string $table,
        private readonly ContentSerializer $contentSerializer
    ) {
    }

    public function byStreamId(string $streamId, int $fromStreamVersion = 0): array
    {
        try {
            $rows = $this->connection->createQueryBuilder()
                ->select('e.*')
                ->from($this->table, 'e')
                ->where('e.streamId = :streamId')
                ->andWhere('e.streamVersion >= :streamVersion')
                ->setParameter('streamId', $streamId, 'uuid')
                ->setParameter('streamVersion', $fromStreamVersion, Types::INTEGER)
                ->executeQuery()
                ->fetchAllAssociative();

            return array_map(
                $this->transformRowToDomainEvent(...),
                $rows
            );
        } catch (Throwable $e) {
            throw new EventStoreException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function append(DomainEvent ...$domainEvents): void
    {
        if (count($domainEvents) === 0) {
            return;
        }

        $params = $types = [];
        foreach ($domainEvents as $domainEvent) {
            $params[] = $domainEvent->streamId;
            $params[] = $domainEvent->streamVersion;
            $params[] = $this->contentSerializer->serialize($domainEvent->content);
            $params[] = $domainEvent->headers;
            array_push($types, 'uuid', Types::INTEGER, Types::STRING, Types::JSON);
        }

        try {
            $this->connection->executeStatement(
                sprintf(
                    'INSERT INTO %s (streamId, streamVersion, content, headers) VALUES %s',
                    $this->table,
                    implode(', ', array_fill(0, count($domainEvents), '(?, ?, ?, ?)'))
                ),
                $params,
                $types
            );
        } catch (UniqueConstraintViolationException $e) {
            throw new DuplicateVersionInStreamException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        } catch (Throwable $e) {
            throw new EventStoreException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function since(int $id, int $limit): array
    {
        try {
            $rows = $this->connection->createQueryBuilder()
                ->select('e.*')
                ->from($this->table, 'e')
                ->where('e.id > :id')
                ->setParameter('id', $id)
                ->setMaxResults($limit)
                ->executeQuery()
                ->fetchAllAssociative();

            return StoredEventFilters::untilGapIsFound(
                $this->transformRowsToStoredEvents($rows),
                $id,
                $this
            );
        } catch (Throwable $e) {
            throw new EventStoreException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function cleanUpTo(int $id): void
    {
        try {
            $this->connection->createQueryBuilder()
                ->delete($this->table)
                ->where('id <= :id')
                ->setParameter('id', $id)
                ->executeStatement();
        } catch (Throwable $e) {
            throw new EventStoreException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function shouldWaitForStoredEventWithId(int $id): bool
    {
        $currentIsolationLevel = $this->connection->getTransactionIsolation();

        $this->connection->setTransactionIsolation(TransactionIsolationLevel::READ_UNCOMMITTED);

        $hasStoredEvent = $this->connection->createQueryBuilder()
                ->select('COUNT(id)')
                ->from($this->table, 'e')
                ->where('e.id = :id')
                ->setParameter('id', $id)
                ->executeQuery()
                ->fetchOne() > 0;

        $this->connection->setTransactionIsolation($currentIsolationLevel);

        return $hasStoredEvent;
    }

    /**
     * Creates a new instance using the given Connection. This is useful for
     * certain sharding scenarios by allowing runtime configuration based on
     * context like tenantId, aggregateId, or region.
     * If the streamId is used as the sharding key for event-sourced streams,
     * a sharding-aware EventStore implementation may be more straightforward.
     */
    public function withConnection(Connection $connection): self
    {
        return new self(
            $connection,
            $this->table,
            $this->contentSerializer
        );
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     *
     * @return StoredEvent[]
     * @throws Throwable
     */
    private function transformRowsToStoredEvents(array $rows): array
    {
        return array_map(
            fn(array $row): StoredEvent => new StoredEvent(
                (int)$row['id'],
                $this->transformRowToDomainEvent($row)
            ),
            $rows
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private function transformRowToDomainEvent(array $row): DomainEvent
    {
        return new DomainEvent(
            (string)$this->connection->convertToPHPValue($row['streamId'], 'uuid'),
            $this->contentSerializer->deserialize((string)$row['content']),
            $this->connection->convertToPHPValue($row['streamVersion'], Types::INTEGER),
            $this->connection->convertToPHPValue($row['headers'], Types::JSON)
        );
    }
}
