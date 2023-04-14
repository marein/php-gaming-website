<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\Enqueue;

use Closure;
use Gaming\Common\MessageBroker\Model\Context\Context;
use Gaming\Common\MessageBroker\Model\Message\Message;

final class ClosureContext implements Context
{
    /**
     * @var Closure(Message): void
     */
    private Closure $request;

    /**
     * @var Closure(Message): void
     */
    private Closure $reply;

    /**
     * @param Closure(Message): void $request
     * @param Closure(Message): void $reply
     */
    public function __construct(Closure $request, Closure $reply)
    {
        $this->request = $request;
        $this->reply = $reply;
    }

    public function request(Message $message): void
    {
        ($this->request)($message);
    }

    public function reply(Message $message): void
    {
        ($this->reply)($message);
    }
}
