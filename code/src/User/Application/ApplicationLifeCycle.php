<?php

namespace Gambling\User\Application;

use Doctrine\DBAL\Driver\Connection;

final class ApplicationLifeCycle
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
     * ApplicationLifeCycle constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->transactionAlreadyStarted = false;
    }

    public function run(callable $action)
    {
        if ($this->transactionAlreadyStarted) {
            return $action();
        } else {
            $this->transactionAlreadyStarted = true;

            try {
                $this->begin();

                $return = $action();

                $this->success();

                return $return;
            } catch (\Exception $exception) {
                $this->fail();

                throw $exception;
            } finally {
                $this->transactionAlreadyStarted = false;
            }
        }
    }

    private function begin(): void
    {
        $this->connection->beginTransaction();
    }

    private function fail(): void
    {
        $this->connection->rollBack();
    }

    private function success(): void
    {
        $this->connection->commit();
    }
}
