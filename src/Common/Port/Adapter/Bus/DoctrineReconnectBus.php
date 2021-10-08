<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Bus;

use Doctrine\DBAL\Connection;
use Gaming\Common\Bus\Bus;
use InvalidArgumentException;

/**
 * This class aims to get around the "Server has gone away" Exception.
 * It saves the time of the last handle. If the current handle reached the maximum number of idle between handles,
 * the connection gets reconnected. So, the value should be lower than the timeout in your database.
 *
 * IMPORTANT: This class should wrap any transactional behaviour, not the other way around.
 */
final class DoctrineReconnectBus implements Bus
{
    /**
     * @var Bus
     */
    private Bus $bus;

    /**
     * @var Connection
     */
    private Connection $connection;

    /**
     * @var int
     */
    private int $idleBetweenHandlesInSeconds;

    /**
     * @var int
     */
    private int $timeOfLastHandle;

    /**
     * DoctrineReconnectBus constructor.
     *
     * @param Bus $bus
     * @param Connection $connection
     * @param int $idleBetweenHandlesInSeconds
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        Bus $bus,
        Connection $connection,
        int $idleBetweenHandlesInSeconds
    ) {
        if ($idleBetweenHandlesInSeconds < 1) {
            throw new InvalidArgumentException(
                'Idle between handles in seconds must be greater than 0.'
            );
        }

        $this->bus = $bus;
        $this->connection = $connection;
        $this->idleBetweenHandlesInSeconds = $idleBetweenHandlesInSeconds;
        $this->timeOfLastHandle = time();
    }

    /**
     * @inheritdoc
     */
    public function handle(object $message)
    {
        $this->reconnectIfTimedOut();

        $return = $this->bus->handle($message);

        $this->timeOfLastHandle = time();

        return $return;
    }

    /**
     * Reconnecting the connection if the last handle is too long ago.
     */
    private function reconnectIfTimedOut(): void
    {
        if (time() - $this->timeOfLastHandle > $this->idleBetweenHandlesInSeconds) {
            $this->connection->close();
            $this->connection->connect();
        }
    }
}
