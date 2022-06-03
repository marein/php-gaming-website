<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Exception;

use Exception;
use Throwable;

class MessageBrokerException extends Exception
{
    public static function fromThrowable(Throwable $throwable): MessageBrokerException
    {
        return new self($throwable->getMessage(), $throwable->getCode(), $throwable);
    }
}
