<?php

namespace Gambling\Common\MessageBroker;

interface Consumer
{
    /**
     * Handle the message with the body and the routing key.
     *
     * @param string $body
     * @param string $routingKey
     */
    public function handle(string $body, string $routingKey): void;

    /**
     * Routing keys to listen to.
     *
     * @return array
     */
    public function routingKeys(): array;

    /**
     * The queue name for this consumer.
     *
     * @return string
     */
    public function queueName(): string;
}
