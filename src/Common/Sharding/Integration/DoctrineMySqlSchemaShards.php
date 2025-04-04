<?php

declare(strict_types=1);

namespace Gaming\Common\Sharding\Integration;

use Doctrine\DBAL\Connection;
use Gaming\Common\Sharding\Exception\ShardingException;
use Gaming\Common\Sharding\Shards;
use Throwable;

/**
 * @implements Shards<Connection>
 */
final class DoctrineMySqlSchemaShards implements Shards
{
    /**
     * @param Shards<string> $shards
     */
    public function __construct(
        private readonly Shards $shards,
        private readonly Connection $connection
    ) {
    }

    public function lookup(string $value): mixed
    {
        try {
            $this->connection->executeStatement(
                'USE ' . $this->connection->quoteIdentifier($this->shards->lookup($value))
            );
        } catch (Throwable $t) {
            throw new ShardingException($t->getMessage(), $t->getCode(), $t);
        }

        return $this->connection;
    }
}
