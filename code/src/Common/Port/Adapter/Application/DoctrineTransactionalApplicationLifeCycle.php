<?php
declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Application;

use Doctrine\DBAL\Driver\Connection;
use Gaming\Common\Application\ApplicationLifeCycle;

final class DoctrineTransactionalApplicationLifeCycle implements ApplicationLifeCycle
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * DoctrineTransactionalApplicationLifeCycle constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritdoc
     */
    public function run(callable $action)
    {
        $this->connection->beginTransaction();

        try {
            $return = $action();

            $this->connection->commit();

            return $return;
        } catch (\Exception $exception) {
            $this->connection->rollBack();

            throw $exception;
        }
    }
}
