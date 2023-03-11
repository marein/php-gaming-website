<?php

declare(strict_types=1);

namespace Gaming\Common\ShardChooser;

interface Storage
{
    public function useShard(string $shard): void;
}
