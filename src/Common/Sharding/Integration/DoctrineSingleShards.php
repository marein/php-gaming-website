<?php

declare(strict_types=1);

namespace Gaming\Common\Sharding\Integration;

use Doctrine\DBAL\Connection;
use Gaming\Common\Sharding\Shards;

/**
 * @implements Shards<Connection>
 */
final class DoctrineSingleShards implements Shards
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public function lookup(string $value): mixed
    {
        return $this->connection;
    }
}
