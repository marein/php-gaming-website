<?php

declare(strict_types=1);

namespace Gaming\Common\Bus\Integration;

use Doctrine\DBAL\Connection;
use Exception;
use Gaming\Common\Bus\Bus;

final class DoctrineTransactionalBus implements Bus
{
    private Bus $bus;

    private Connection $connection;

    public function __construct(Bus $bus, Connection $connection)
    {
        $this->bus = $bus;
        $this->connection = $connection;
    }

    public function handle(object $message): mixed
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
