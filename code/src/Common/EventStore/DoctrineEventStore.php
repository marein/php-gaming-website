<?php

namespace Gambling\Common\EventStore;

use Doctrine\DBAL\Connection;
use Gambling\Common\Domain\DomainEvent;

final class DoctrineEventStore implements EventStore
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $table;

    /**
     * DoctrineEventStore constructor.
     *
     * @param Connection $connection
     * @param string     $table
     */
    public function __construct(Connection $connection, string $table)
    {
        $this->connection = $connection;
        $this->table = $table;
    }

    /**
     * @inheritdoc
     */
    public function storedEventsSince(int $id, int $limit): array
    {
        $rows = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->table, 'e')
            ->where('e.id > :id')
            ->setParameter(':id', $id)
            ->setMaxResults($limit)
            ->execute()
            ->fetchAll();

        return $this->transformRowsToStoredEvents($rows);
    }

    /**
     * @inheritdoc
     */
    public function append(DomainEvent $domainEvent): void
    {
        $this->connection->insert($this->table, [
            'name'       => $domainEvent->name(),
            'payload'    => $domainEvent->payload(),
            'occurredOn' => $domainEvent->occurredOn()
        ], [
            'string',
            'json',
            'datetime_immutable'
        ]);
    }

    /**
     * Transform the sql row to a stored event instance.
     *
     * @param array $rows
     *
     * @return StoredEvent[]
     */
    private function transformRowsToStoredEvents(array $rows): array
    {
        return array_map(function ($row) {
            return new StoredEvent(
                $row['id'],
                $row['name'],
                $row['payload'],
                new \DateTimeImmutable($row['occurredOn'])
            );
        }, $rows);
    }
}
