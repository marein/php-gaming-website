<?php

declare(strict_types=1);

namespace Gaming\Common\Bus\Integration;

use Doctrine\DBAL\Connection;
use Gaming\Common\Bus\Bus;
use Gaming\Common\Bus\Request;

final class DoctrineTransactionalBus implements Bus
{
    public function __construct(
        private readonly Bus $bus,
        private readonly Connection $connection
    ) {
    }

    public function handle(Request $request): mixed
    {
        return $this->connection->transactional(
            fn() => $this->bus->handle($request)
        );
    }
}
