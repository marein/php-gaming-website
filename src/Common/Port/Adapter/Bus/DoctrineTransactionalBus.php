<?php
declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Bus;

use Doctrine\DBAL\Driver\Connection;
use Exception;
use Gaming\Common\Bus\Bus;

final class DoctrineTransactionalBus implements Bus
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
     * DoctrineTransactionalBus constructor.
     *
     * @param Bus        $bus        The bus who is being wrapped.
     * @param Connection $connection The connection which handles the transaction.
     */
    public function __construct(Bus $bus, Connection $connection)
    {
        $this->bus = $bus;
        $this->connection = $connection;
    }

    /**
     * @inheritdoc
     */
    public function handle(object $message)
    {
        $this->connection->beginTransaction();

        try {
            $return = $this->bus->handle($message);

            $this->connection->commit();

            return $return;
        } catch (Exception $exception) {
            $this->connection->rollBack();

            throw $exception;
        }
    }
}
