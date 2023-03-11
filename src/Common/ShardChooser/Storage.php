<?php

declare(strict_types=1);

namespace Gaming\Common\ShardChooser;

use Gaming\Common\ShardChooser\Exception\ShardChooserException;

interface Storage
{
    /**
     * @throws ShardChooserException
     */
    public function useShard(string $shard): void;
}
