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
final class DoctrineSchemaShards implements Shards
{
    private string $lastShard;

    /**
     * @param Shards<string> $shards
     */
    public function __construct(
        private readonly Shards $shards,
        private readonly Connection $connection,
        private readonly string $statement = 'USE `%s`'
    ) {
        $this->lastShard = '';
    }

    public function lookup(string $value): mixed
    {
        $shard = $this->shards->lookup($value);
        if ($this->lastShard === $shard) {
            return $this->connection;
        }

        try {
            $this->connection->executeStatement(sprintf($this->statement, $shard));
            $this->lastShard = $shard;

            return $this->connection;
        } catch (Throwable $t) {
            throw ShardingException::fromThrowable($t);
        }
    }
}
