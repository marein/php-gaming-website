<?php

declare(strict_types=1);

namespace Gaming\Common\ShardChooser\Integration;

use Gaming\Common\ShardChooser\Storage;

final class TestStorage implements Storage
{
    public function __construct(
        public string $usedShard = ''
    ) {
    }

    public function useShard(string $shard): void
    {
        $this->usedShard = $shard;
    }

    public function resetUsedShard(): void
    {
        $this->usedShard = '';
    }
}
