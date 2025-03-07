<?php

declare(strict_types=1);

namespace Gaming\Chat\Infrastructure\Messaging;

use Gaming\Common\BrowserNotifier\BrowserNotifier;
use Gaming\Common\MessageBroker\Context;
use Gaming\Common\MessageBroker\Message;
use Gaming\Common\MessageBroker\MessageHandler;
use GamingPlatform\Api\Chat\V1\ChatV1Factory;

final class PublishMessageBrokerEventsToBrowserMessageHandler implements MessageHandler
{
    public function __construct(
        private readonly BrowserNotifier $browserNotifier
    ) {
    }

    public function handle(Message $message, Context $context): void
    {
        match ($message->name()) {
            'Chat.MessageWritten' => $this->browserNotifier->publish(
                ['chat-' . $message->streamId()],
                $message->name(),
                ChatV1Factory::createMessageWritten($message->body())->serializeToJsonString()
            ),
            default => true
        };
    }
}
