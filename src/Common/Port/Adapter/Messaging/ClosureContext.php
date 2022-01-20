<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Messaging;

use Closure;
use Gaming\Common\MessageBroker\Model\Context\Context;
use Gaming\Common\MessageBroker\Model\Message\Message;

final class ClosureContext implements Context
{
    /**
     * @var Closure(Message): void
     */
    private Closure $publish;

    /**
     * @var Closure(Message): void
     */
    private Closure $reply;

    /**
     * @param Closure(Message): void $publish
     * @param Closure(Message): void $reply
     */
    public function __construct(Closure $publish, Closure $reply)
    {
        $this->publish = $publish;
        $this->reply = $reply;
    }

    public function publish(Message $message): void
    {
        ($this->publish)($message);
    }

    public function reply(Message $message): void
    {
        ($this->reply)($message);
    }
}
