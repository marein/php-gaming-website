<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\MessageRouter;

final readonly class Route
{
    public function __construct(
        public string $exchange,
        public string $routingKey
    ) {
    }
}
