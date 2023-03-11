<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\EventStore;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Gaming\Common\Domain\DomainEvent;
use Gaming\Common\EventStore\EventStore;
use Gaming\Common\EventStore\Exception\EventStoreException;
use Gaming\Common\EventStore\GapDetection;
use Gaming\Common\EventStore\PollableEventStore;
use Gaming\Common\EventStore\StoredEvent;
use Gaming\Common\EventStore\StoredEventFilters;
use Gaming\Common\Normalizer\Normalizer;
use Psr\Clock\ClockInterface;
use Throwable;

final class DoctrineEventStore implements EventStore, PollableEventStore
{
    private const SELECT = 'e.id, e.event, e.occurredOn';

    private readonly GapDetection $gapDetection;

    public function __construct(
        private readonly Connection $connection,
        private readonly string $table,
        private readonly Normalizer $normalizer,
        private readonly ClockInterface $clock
    ) {
        $this->gapDetection = new DoctrineWaitForUncommittedStoredEventsGapDetection(
            $this->connection,
            $this->table,
            'id'
        );
    }

    public function byAggregateId(string $aggregateId, int $sinceId = 0): array
    {
        try {
            $rows = $this->connection->fetchAllAssociative(
                'SELECT ' . self::SELECT . ' FROM ' . $this->table . ' e WHERE e.aggregateId = ? AND e.id > 0',
                [$aggregateId, $sinceId],
                ['uuid', Types::INTEGER]
            );

            return $this->transformRowsToStoredEvents($rows);
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
        try {
            foreach ($domainEvents as $domainEvent) {
                $this->connection->insert(
                    $this->table,
                    [
                        'aggregateId' => $domainEvent->aggregateId(),
                        'event' => $this->normalizer->normalize($domainEvent, DomainEvent::class),
                        'occurredOn' => $this->clock->now()
                    ],
                    [
                        'uuid',
                        'json',
                        'datetime_immutable'
                    ]
                );
            }
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
            $rows = $this->connection->fetchAllAssociative(
                'SELECT ' . self::SELECT . ' FROM ' . $this->table . ' e WHERE e.id > ? LIMIT ?',
                [$id, $limit],
                [Types::INTEGER, Types::INTEGER]
            );

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
