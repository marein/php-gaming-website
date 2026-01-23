<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Port\Adapter\Messaging;

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
            'TicTacToe.ChallengeOpened' => $this->handleChallengeOpened($message),
            'TicTacToe.ChallengeAccepted',
            'TicTacToe.ChallengeWithdrawn' => $this->browserNotifier->publish(
                ['ttt-lobby'],
                $message->name(),
                $message->body()
            ),
            default => true
        };
    }

    private function handleChallengeOpened(Message $message): void
    {
        $body = json_decode($message->body(), true, flags: JSON_THROW_ON_ERROR);
        $body['playerUsername'] = $this->usernames->byIds([$body['playerId']])[$body['playerId']];

        $this->browserNotifier->publish(
            ['ttt-lobby'],
            $message->name(),
            json_encode($body, JSON_THROW_ON_ERROR)
        );
    }
}
