<?php

declare(strict_types=1);

namespace Gaming\Common\Sharding;

use Gaming\Common\Sharding\Exception\ShardingException;

interface Shards
{
    /**
     * @throws ShardingException
     */
    public function lookup(string $value): string;
}
