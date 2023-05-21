<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\MessageRouter;

use Gaming\Common\MessageBroker\Message;

final readonly class RouteMessagesToExchange implements MessageRouter
{
    public function __construct(
        private string $exchange
    ) {
    }

    public function route(Message $message): Route
    {
        return new Route(
            $this->exchange,
            $message->name()
        );
    }
}
