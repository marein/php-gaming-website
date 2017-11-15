<?php

namespace Gambling\Common\Port\Adapter\Messaging;

interface MessageBroker
{
    /**
     * Publish the message body with routing key to the configured exchange.
     *
     * @param string $body
     * @param string $routingKey
     */
    public function publish(string $body, string $routingKey);

    /**
     * Consume the given queue name and create bindings to configured exchange via routing keys.
     * The callback is invoked after each received message.
     *
     * @param string   $queueName
     * @param array    $routingKeys
     * @param callable $callback
     */
    public function consume(string $queueName, array $routingKeys, callable $callback);
}
