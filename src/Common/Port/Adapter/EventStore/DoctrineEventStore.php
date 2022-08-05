<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\EventStore;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Gaming\Common\Clock\Clock;
use Gaming\Common\Domain\DomainEvent;
use Gaming\Common\EventStore\EventStore;
use Gaming\Common\EventStore\Exception\EventStoreException;
use Gaming\Common\EventStore\GapDetection;
use Gaming\Common\EventStore\PollableEventStore;
use Gaming\Common\EventStore\StoredEvent;
use Gaming\Common\EventStore\StoredEventFilters;
use Gaming\Common\Normalizer\Normalizer;
use Throwable;

final class DoctrineEventStore implements EventStore, PollableEventStore
{
    private const SELECT = 'e.id, e.event, e.occurredOn';

    private readonly GapDetection $gapDetection;

    public function __construct(
        private readonly Connection $connection,
        private readonly string $table,
        private readonly Normalizer $normalizer
    ) {
        $this->gapDetection = new DoctrineIsolationLevelGapDetection(
            $this->connection,
            $this->table,
            'id'
        );
    }

    public function byAggregateId(string $aggregateId, int $sinceId = 0): array
    {
        try {
            $rows = $this->connection->createQueryBuilder()
                ->select(self::SELECT)
                ->from($this->table, 'e')
                ->where('e.aggregateId = :aggregateId')
                ->andWhere('e.id > :id')
                ->setParameter('aggregateId', $aggregateId, 'uuid')
                ->setParameter('id', $sinceId)
                ->executeQuery()
                ->fetchAllAssociative();

            return $this->transformRowsToStoredEvents($rows);
        } catch (Throwable $e) {
            throw new EventStoreException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function append(DomainEvent $domainEvent): void
    {
        try {
            $this->connection->insert(
                $this->table,
                [
                    'aggregateId' => $domainEvent->aggregateId(),
                    'event' => $this->normalizer->normalize($domainEvent, DomainEvent::class),
                    'occurredOn' => Clock::instance()->now()
                ],
                [
                    'uuid',
                    'json',
                    'datetime_immutable'
                ]
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
                ->select(self::SELECT)
                ->from($this->table, 'e')
                ->where('e.id > :id')
                ->setParameter('id', $id)
                ->setMaxResults($limit)
                ->executeQuery()
                ->fetchAllAssociative();

            return StoredEventFilters::untilGapIsFound(
                $this->transformRowsToStoredEvents($rows),
                $id,
                $this->gapDetection
            );
        } catch (Throwable $e) {
            throw new EventStoreException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
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
                new DateTimeImmutable($row['occurredOn']),
                $this->normalizer->denormalize(
                    json_decode(
                        $row['event'],
                        true,
                        512,
                        JSON_THROW_ON_ERROR
                    ),
                    DomainEvent::class
                )
            ),
            $rows
        );
    }
}
