<?php
declare(strict_types=1);

namespace Gaming\Common\MessageBroker;

interface MessageBroker
{
    /**
     * Publish the message body with routing key to the configured exchange.
     *
     * @param string $body
     * @param string $routingKey
     */
    public function publish(string $body, string $routingKey): void;

    /**
     * Consume with the given consumer.
     *
     * @param Consumer $consumer
     */
    public function consume(Consumer $consumer): void;
}
