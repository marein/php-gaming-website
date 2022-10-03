<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Messaging;

use Gaming\Common\MessageBroker\Integration\AmqpLib\MessageTranslator\MessageTranslator;
use Gaming\Common\MessageBroker\Model\Message\Message;
use PhpAmqpLib\Message\AMQPMessage;

final class GamingMessageTranslator implements MessageTranslator
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
            json_encode(
                [(string)$message->name(), $message->body()],
                JSON_THROW_ON_ERROR
            ),
            $this->properties
        );
    }
}
