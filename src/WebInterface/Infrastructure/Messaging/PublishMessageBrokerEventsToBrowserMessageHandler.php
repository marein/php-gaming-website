<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\Messaging;

use Gaming\Common\MessageBroker\Context;
use Gaming\Common\MessageBroker\Message;
use Gaming\Common\MessageBroker\MessageHandler;
use Gaming\WebInterface\Application\BrowserNotifier;

final class PublishMessageBrokerEventsToBrowserMessageHandler implements MessageHandler
{
    public function __construct(
        private readonly BrowserNotifier $browserNotifier
    ) {
    }

    public function handle(Message $message, Context $context): void
    {
        $payload = array_merge(
            json_decode($message->body(), true, 512, JSON_THROW_ON_ERROR),
            ['eventName' => $message->name()]
        );
        $json = json_encode($payload, JSON_THROW_ON_ERROR);

        match ($message->name()) {
            'ConnectFour.GameOpened', 'ConnectFour.GameAborted' => $this->browserNotifier->publish(
                ['lobby'],
                $json
            ),
            'ConnectFour.GameResigned',
            'ConnectFour.GameWon',
            'ConnectFour.GameDrawn',
            'ConnectFour.PlayerMoved',
            'ConnectFour.ChatAssigned' => $this->browserNotifier->publish(
                ['connect-four-' . $payload['gameId']],
                $json
            ),
            'ConnectFour.PlayerJoined' => $this->browserNotifier->publish(
                ['lobby', 'connect-four-' . $payload['gameId']],
                $json
            ),
            'Chat.MessageWritten' => $this->browserNotifier->publish(
                ['chat-' . $payload['chatId']],
                $json
            ),
            default => true
        };
    }
}
