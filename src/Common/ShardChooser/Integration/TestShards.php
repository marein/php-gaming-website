<?php

declare(strict_types=1);

namespace Gaming\Common\ShardChooser\Integration;

use Gaming\Common\ShardChooser\Shards;

final class TestShards implements Shards
{
    /**
     * @param array<string, string> $shards
     */
    public function __construct(
        private readonly array $shards
    ) {
    }

    public function fromValue(string $value): string
    {
        return $this->shards[$value] ?? 'default';
    }
}
