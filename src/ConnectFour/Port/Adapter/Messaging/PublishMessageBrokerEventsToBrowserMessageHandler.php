<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Messaging;

use Gaming\Common\BrowserNotifier\BrowserNotifier;
use Gaming\Common\MessageBroker\Context;
use Gaming\Common\MessageBroker\Message;
use Gaming\Common\MessageBroker\MessageHandler;
use Gaming\Common\Usernames\Usernames;

final class PublishMessageBrokerEventsToBrowserMessageHandler implements MessageHandler
{
    public function __construct(
        private readonly BrowserNotifier $browserNotifier,
        private readonly Usernames $usernames
    ) {
    }

    public function handle(Message $message, Context $context): void
    {
        match ($message->name()) {
            'ConnectFour.GameOpened' => $this->handleGameOpened($message),
            'ConnectFour.GameResigned',
            'ConnectFour.GameTimedOut',
            'ConnectFour.GameWon',
            'ConnectFour.GameDrawn',
            'ConnectFour.PlayerMoved',
            'ConnectFour.ChatAssigned' => $this->browserNotifier->publish(
                ['connect-four-' . $message->streamId()],
                $message->name(),
                $message->body()
            ),
            'ConnectFour.GameAborted' => $this->browserNotifier->publish(
                ['lobby', 'connect-four-' . $message->streamId()],
                $message->name(),
                $message->body()
            ),
            'ConnectFour.PlayerJoined' => $this->handlePlayerJoined($message),
            default => true
        };
    }

    private function handleGameOpened(Message $message): void
    {
        $body = json_decode($message->body(), true, flags: JSON_THROW_ON_ERROR);
        $body['playerUsername'] = $this->usernames->byIds([$body['playerId']])[$body['playerId']] ?? null;

        $this->browserNotifier->publish(
            ['lobby'],
            $message->name(),
            json_encode($body, JSON_THROW_ON_ERROR)
        );
    }

    private function handlePlayerJoined(Message $message): void
    {
        $body = json_decode($message->body(), true, flags: JSON_THROW_ON_ERROR);

        $usernames = $this->usernames->byIds([$body['redPlayerId'], $body['yellowPlayerId']]);
        $body['redPlayerUsername'] = $usernames[$body['redPlayerId']] ?? null;
        $body['yellowPlayerUsername'] = $usernames[$body['yellowPlayerId']] ?? null;

        $this->browserNotifier->publish(
            ['lobby', 'connect-four-' . $message->streamId()],
            $message->name(),
            json_encode($body, JSON_THROW_ON_ERROR)
        );
    }
}
