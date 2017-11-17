<?php

namespace Gambling\User\Application;

use Doctrine\DBAL\Driver\Connection;
use Gambling\Common\Application\ApplicationLifeCycle;
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
        return new DoctrineTransactionalApplicationLifeCycle(
            $this->connection
        );
    }
}
