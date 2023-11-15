<?php

declare(strict_types=1);

namespace Gaming\Common\Sharding;

use Gaming\Common\Sharding\Exception\ShardingException;

/**
 * @template T
 */
interface Shards
{
    /**
     * @return T
     * @throws ShardingException
     */
    public function lookup(string $value): mixed;
}
