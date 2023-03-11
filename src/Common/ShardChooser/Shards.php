<?php

declare(strict_types=1);

namespace Gaming\Common\ShardChooser;

use Gaming\Common\ShardChooser\Exception\ShardChooserException;

interface Shards
{
    /**
     * @throws ShardChooserException
     */
    public function fromValue(string $value): string;
}
