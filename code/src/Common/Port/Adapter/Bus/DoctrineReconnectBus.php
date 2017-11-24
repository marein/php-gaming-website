<?php

namespace Gambling\Common\Port\Adapter\Bus;

use Doctrine\DBAL\Connection;
use Gambling\Common\Bus\Bus;

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
    private $bus;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var int
     */
    private $idleBetweenHandlesInSeconds;

    /**
     * @var int
     */
    private $timeOfLastHandle;

    /**
     * DoctrineReconnectBus constructor.
     *
     * @param Bus        $bus
     * @param Connection $connection
     * @param int        $idleBetweenHandlesInSeconds
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        Bus $bus,
        Connection $connection,
        int $idleBetweenHandlesInSeconds
    ) {
        if ($idleBetweenHandlesInSeconds < 1) {
            throw new \InvalidArgumentException(
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
    public function handle($command)
    {
        $this->reconnectIfTimedOut();

        $return = $this->bus->handle($command);

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
