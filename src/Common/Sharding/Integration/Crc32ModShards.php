<?php

declare(strict_types=1);

namespace Gaming\Common\Sharding\Integration;

use Gaming\Common\Sharding\Exception\ShardingException;
use Gaming\Common\Sharding\Shards;

/**
 * This implementation causes problems in the event of re-sharding,
 * because it's very likely that values will be reassigned to another shard.
 * This can lead to more network calls until everything is back in place.
 * It's therefore not suitable for every use case and should be used with caution.
 * A better alternative would be to use a consistent hashing algorithm,
 * which would reduce the likelihood of values being reassigned to another shard.
 *
 * @template T
 * @implements Shards<T>
 */
final class Crc32ModShards implements Shards
{
    /**
     * @param T[] $shards
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

    public function lookup(string $value): mixed
    {
        return $this->shards[crc32($value) % count($this->shards)];
    }
}
