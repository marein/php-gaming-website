<?php

declare(strict_types=1);

namespace Gaming\Common\ShardChooser\Integration;

use Gaming\Common\ShardChooser\Exception\ShardChooserException;
use Gaming\Common\ShardChooser\Shards;

final class Crc32ModShards implements Shards
{
    /**
     * @param string[] $shards
     *
     * @throws ShardChooserException
     */
    public function __construct(
        private readonly array $shards
    ) {
        if (count($this->shards) === 0) {
            throw new ShardChooserException('At least one shard must be specified.');
        }
    }

    public function fromValue(string $value): string
    {
        return $this->shards[crc32($value) % count($this->shards)];
    }
}
