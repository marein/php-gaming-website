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
     * @var bool
     */
    private $transactionAlreadyStarted;

    /**
     * DoctrineTransactionalApplicationLifeCycle constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->transactionAlreadyStarted = false;
    }

    /**
     * @inheritdoc
     */
    public function run(callable $action)
    {
        if ($this->transactionAlreadyStarted) {
            return $action();
        }

        $this->transactionAlreadyStarted = true;

        try {
            $this->connection->beginTransaction();

            $return = $action();

            $this->connection->commit();

            return $return;
        } catch (\Exception $exception) {
            $this->connection->rollBack();

            throw $exception;
        } finally {
            $this->transactionAlreadyStarted = false;
        }
    }
}
