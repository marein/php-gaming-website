<?php
declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Application;

use Doctrine\DBAL\Connection;
use Gaming\Common\Application\ApplicationLifeCycle;

/**
 * This class aims to get around the "Server has gone away" Exception.
 * It saves the time of the last run. If the current run reached the maximum number of idle between runs,
 * the connection gets reconnected. So, the value should be lower than the timeout in your database.
 *
 * IMPORTANT: This class should wrap any transactional behaviour, not the other way around.
 */
final class DoctrineReconnectApplicationLifeCycle implements ApplicationLifeCycle
{
    /**
     * @var ApplicationLifeCycle
     */
    private $applicationLifeCycle;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var int
     */
    private $idleBetweenRunsInSeconds;

    /**
     * @var int
     */
    private $timeOfLastRun;

    /**
     * DoctrineReconnectApplicationLifeCycle constructor.
     *
     * @param ApplicationLifeCycle $applicationLifeCycle
     * @param Connection           $connection
     * @param int                  $idleBetweenRunsInSeconds
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        ApplicationLifeCycle $applicationLifeCycle,
        Connection $connection,
        int $idleBetweenRunsInSeconds
    ) {
        if ($idleBetweenRunsInSeconds < 1) {
            throw new \InvalidArgumentException(
                'Idle between runs in seconds must be greater than 0.'
            );
        }

        $this->applicationLifeCycle = $applicationLifeCycle;
        $this->connection = $connection;
        $this->idleBetweenRunsInSeconds = $idleBetweenRunsInSeconds;
        $this->timeOfLastRun = time();
    }

    /**
     * @inheritdoc
     */
    public function run(callable $action)
    {
        $this->reconnectIfTimedOut();

        $return = $this->applicationLifeCycle->run($action);

        $this->timeOfLastRun = time();

        return $return;
    }

    /**
     * Reconnecting the connection if the last run is too long ago.
     */
    private function reconnectIfTimedOut(): void
    {
        if (time() - $this->timeOfLastRun > $this->idleBetweenRunsInSeconds) {
            $this->connection->close();
            $this->connection->connect();
        }
    }
}
