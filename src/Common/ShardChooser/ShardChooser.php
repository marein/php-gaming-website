<?php

declare(strict_types=1);

namespace Gaming\Common\ShardChooser;

final class ShardChooser
{
    private ?string $selectedShard;

    /**
     * @param string[] $shards
     */
    public function __construct(
        private readonly Storage $storage,
        private readonly array $shards
    ) {
        $this->selectedShard = null;
    }

    public function select(string $value): void
    {
        $shard = $this->shards[crc32($value) % count($this->shards)];
        if ($this->selectedShard === $shard) {
            return;
        }

        $this->storage->useShard($shard);
        $this->selectedShard = $shard;
    }
}
