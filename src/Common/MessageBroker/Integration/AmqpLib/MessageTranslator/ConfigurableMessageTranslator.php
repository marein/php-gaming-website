<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\MessageTranslator;

use Gaming\Common\MessageBroker\Message;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

final class ConfigurableMessageTranslator implements MessageTranslator
{
    /**
     * @param array<string, mixed> $properties
     */
    public function __construct(
        private readonly array $properties
    ) {
    }

    public function createAmqpMessageFromMessage(Message $message): AMQPMessage
    {
        return new AMQPMessage(
            $message->body(),
            array_merge(
                $this->properties,
                [
                    'type' => $message->name(),
                    'application_headers' => new AMQPTable($message->headers())
                ]
            )
        );
    }

    public function createMessageFromAmqpMessage(AMQPMessage $message): Message
    {
        return new Message(
            (string)$message->get('type'),
            $message->getBody(),
            $message->has('application_headers') ? $message->get('application_headers')->getNativeData() : []
        );
    }
}
