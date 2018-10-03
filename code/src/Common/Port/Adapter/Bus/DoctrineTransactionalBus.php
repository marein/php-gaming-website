<?php
declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Bus;

use Doctrine\DBAL\Driver\Connection;
use Gaming\Common\Bus\Bus;

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
    public function handle(object $message)
    {
        if ($this->transactionAlreadyStarted) {
            return $this->bus->handle($message);
        }

        $this->transactionAlreadyStarted = true;

        try {
            $this->connection->beginTransaction();

            $return = $this->bus->handle($message);

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
