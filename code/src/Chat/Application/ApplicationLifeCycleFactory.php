<?php

namespace Gambling\Chat\Application;

use Doctrine\DBAL\Connection;
use Gambling\Common\Application\ApplicationLifeCycle;
use Gambling\Common\Application\RetryApplicationLifeCycle;
use Gambling\Common\Port\Adapter\Application\DoctrineReconnectApplicationLifeCycle;
use Gambling\Common\Port\Adapter\Application\DoctrineTransactionalApplicationLifeCycle;

final class ApplicationLifeCycleFactory
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * ApplicationLifeCycleFactory constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return ApplicationLifeCycle
     */
    public function create(): ApplicationLifeCycle
    {
        return new DoctrineReconnectApplicationLifeCycle(
            new RetryApplicationLifeCycle(
                new DoctrineTransactionalApplicationLifeCycle(
                    $this->connection
                ),
                3
            ),
            $this->connection,
            60
        );
    }
}
