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
        private readonly array $properties,
        private readonly string $streamIdHeader
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
                    'application_headers' => new AMQPTable([$this->streamIdHeader => $message->streamId()])
                ]
            )
        );
    }

    public function createMessageFromAmqpMessage(AMQPMessage $message): Message
    {
        $headers = $message->has('application_headers') ? $message->get('application_headers')->getNativeData() : [];

        return new Message(
            (string)$message->get('type'),
            $message->getBody(),
            $headers[$this->streamIdHeader] ?? ''
        );
    }
}
