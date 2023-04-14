<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\Integration\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Exception;
use Gaming\Common\EventStore\EventStorePointer;
use Gaming\Common\EventStore\Exception\FailedRetrieveMostRecentPublishedStoredEventIdException;
use Gaming\Common\EventStore\Exception\FailedTrackMostRecentPublishedStoredEventIdException;

final class DoctrineMysqlEventStorePointer implements EventStorePointer
{
    public function __construct(
        private readonly Connection $connection,
        private readonly string $tableName,
        private readonly string $pointerName
    ) {
    }

    public function trackMostRecentPublishedStoredEventId(int $id): void
    {
        try {
            $this->connection->executeStatement(
                'INSERT INTO ' . $this->tableName . ' (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?',
                [
                    $this->pointerName,
                    $id,
                    $id
                ],
                [
                    Types::STRING,
                    Types::INTEGER,
                    Types::INTEGER
                ]
            );
        } catch (Exception $e) {
            throw new FailedTrackMostRecentPublishedStoredEventIdException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function retrieveMostRecentPublishedStoredEventId(): int
    {
        try {
            $result = $this->connection->executeQuery(
                'SELECT value FROM ' . $this->tableName . ' WHERE name = ?',
                [
                    $this->pointerName
                ],
                [
                    Types::STRING
                ]
            );

            return (int)$result->fetchOne();
        } catch (Exception $e) {
            throw new FailedRetrieveMostRecentPublishedStoredEventIdException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
