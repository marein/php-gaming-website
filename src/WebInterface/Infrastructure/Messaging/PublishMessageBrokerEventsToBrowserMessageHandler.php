<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\Messaging;

use Gaming\Common\MessageBroker\Context;
use Gaming\Common\MessageBroker\Message;
use Gaming\Common\MessageBroker\MessageHandler;
use Gaming\WebInterface\Application\BrowserNotifier;
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
            'ConnectFour.GameOpened',
            'ConnectFour.GameAborted' => $this->browserNotifier->publish(
                ['lobby'],
                $message->name(),
                $message->body()
            ),
            'ConnectFour.GameResigned',
            'ConnectFour.GameWon',
            'ConnectFour.GameDrawn',
            'ConnectFour.PlayerMoved',
            'ConnectFour.ChatAssigned' => $this->browserNotifier->publish(
                ['connect-four-' . $message->streamId()],
                $message->name(),
                $message->body()
            ),
            'ConnectFour.PlayerJoined' => $this->browserNotifier->publish(
                ['lobby', 'connect-four-' . $message->streamId()],
                $message->name(),
                $message->body()
            ),
            'Chat.MessageWritten' => $this->browserNotifier->publish(
                ['chat-' . $message->streamId()],
                $message->name(),
                ChatV1Factory::createMessageWritten($message->body())->serializeToJsonString()
            ),
            default => true
        };
    }
}
