<?php

namespace Gambling\Common\Port\Adapter\Bus;

use Doctrine\DBAL\Driver\Connection;
use Gambling\Common\Bus\Bus;

final class DoctrineTransactionalBus implements Bus
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
     * @var bool
     */
    private $transactionAlreadyStarted;

    /**
     * DoctrineTransactionalBus constructor.
     *
     * @param Bus        $bus        The bus who is being wrapped.
     * @param Connection $connection The connection which handles the transaction.
     */
    public function __construct(Bus $bus, Connection $connection)
    {
        $this->bus = $bus;
        $this->connection = $connection;
        $this->transactionAlreadyStarted = false;
    }

    /**
     * @inheritdoc
     */
    public function handle($command)
    {
        if ($this->transactionAlreadyStarted) {
            return $this->bus->handle($command);
        } else {
            $this->transactionAlreadyStarted = true;

            try {
                $this->begin();

                $return = $this->bus->handle($command);

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
