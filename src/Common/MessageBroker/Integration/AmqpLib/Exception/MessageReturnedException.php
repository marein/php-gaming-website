<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\Exception;

use Gaming\Common\MessageBroker\Exception\MessageBrokerException;

final class MessageReturnedException extends MessageBrokerException
{
    public function __construct(
        private readonly int $replyCode,
        private readonly string $replyText,
        private readonly string $exchange,
        private readonly string $routingKey
    ) {
        parent::__construct(
            sprintf(
                'Message with routing key "%s" to exchange "%s" returned with code "%d" and message "%s".',
                $this->routingKey,
                $this->exchange,
                $this->replyCode,
                $this->replyText
            )
        );
    }

    public function replyCode(): int
    {
        return $this->replyCode;
    }

    public function replyText(): string
    {
        return $this->replyText;
    }

    public function exchange(): string
    {
        return $this->exchange;
    }

    public function routingKey(): string
    {
        return $this->routingKey;
    }
}
