<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Messaging;

use Gaming\Common\BrowserNotifier\BrowserNotifier;
use Gaming\Common\MessageBroker\Context;
use Gaming\Common\MessageBroker\Message;
use Gaming\Common\MessageBroker\MessageHandler;

final class PublishMessageBrokerEventsToBrowserMessageHandler implements MessageHandler
{
    public function __construct(
        private readonly BrowserNotifier $browserNotifier
    ) {
    }

    public function handle(Message $message, Context $context): void
    {
        match ($message->name()) {
            'ConnectFour.GameOpened' => $this->browserNotifier->publish(
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
            'ConnectFour.GameAborted',
            'ConnectFour.PlayerJoined' => $this->browserNotifier->publish(
                ['lobby', 'connect-four-' . $message->streamId()],
                $message->name(),
                $message->body()
            ),
            default => true
        };
    }
}
