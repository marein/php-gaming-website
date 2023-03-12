<?php

declare(strict_types=1);

namespace Gaming\Common\Sharding\Integration;

use Gaming\Common\Sharding\Exception\ShardingException;
use Gaming\Common\Sharding\Shards;

final class Crc32ModShards implements Shards
{
    /**
     * @param string[] $shards
     *
     * @throws ShardingException
     */
    public function __construct(
        private readonly array $shards
    ) {
        if (count($this->shards) === 0) {
            throw new ShardingException('At least one shard must be specified.');
        }
    }

    public function lookup(string $value): string
    {
        return $this->shards[crc32($value) % count($this->shards)];
    }
}
