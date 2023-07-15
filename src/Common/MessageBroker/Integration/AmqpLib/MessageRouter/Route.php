<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\MessageRouter;

final class Route
{
    public function __construct(
        public readonly string $exchange,
        public readonly string $routingKey
    ) {
    }
}
