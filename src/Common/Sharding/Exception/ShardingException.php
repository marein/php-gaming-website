<?php

declare(strict_types=1);

namespace Gaming\Common\Sharding\Exception;

use Exception;
use Throwable;

final class ShardingException extends Exception
{
    public static function fromThrowable(Throwable $throwable): self
    {
        return new self($throwable->getMessage(), $throwable->getCode(), $throwable);
    }
}
