<?php

declare(strict_types=1);

namespace Gaming\Common\ShardChooser;

use Gaming\Common\ShardChooser\Exception\ShardChooserException;

final class ShardChooser
{
    private ?string $selectedShard;

    public function __construct(
        private readonly Storage $storage,
        private readonly Shards $shards
    ) {
        $this->selectedShard = null;
    }

    /**
     * @throws ShardChooserException
     */
    public function select(string $value): void
    {
        $shard = $this->shards->fromValue($value);
        if ($this->selectedShard === $shard) {
            return;
        }

        $this->storage->useShard($shard);
        $this->selectedShard = $shard;
    }
}
