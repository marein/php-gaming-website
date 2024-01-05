<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\Messaging;

use Gaming\Common\MessageBroker\Context;
use Gaming\Common\MessageBroker\Message;
use Gaming\Common\MessageBroker\MessageHandler;
use Gaming\WebInterface\Application\BrowserNotifier;
use GamingPlatform\Api\Chat\V1\MessageWritten;

final class PublishMessageBrokerEventsToBrowserMessageHandler implements MessageHandler
{
    public function __construct(
        private readonly BrowserNotifier $browserNotifier
    ) {
    }

    public function handle(Message $message, Context $context): void
    {
        match ($message->name()) {
            'ConnectFour.GameOpened' => $this->handleGameOpened($message),
            'ConnectFour.GameAborted' => $this->handleGameAborted($message),
            'ConnectFour.GameResigned' => $this->handleGameResigned($message),
            'ConnectFour.GameWon' => $this->handleGameWon($message),
            'ConnectFour.GameDrawn' => $this->handleGameDrawn($message),
            'ConnectFour.PlayerMoved' => $this->handlePlayerMoved($message),
            'ConnectFour.ChatAssigned' => $this->handleChatAssigned($message),
            'ConnectFour.PlayerJoined' => $this->handlePlayerJoined($message),
            'Chat.MessageWritten' => $this->handleMessageWritten($message),
            default => true
        };
    }

    private function handleGameOpened(Message $message): void
    {
        $this->browserNotifier->publish(
            ['lobby'],
            $message->name(),
            $message->body()
        );
    }

    private function handleGameAborted(Message $message): void
    {
        $this->browserNotifier->publish(
            ['lobby'],
            $message->name(),
            $message->body()
        );
    }

    private function handlePlayerJoined(Message $message): void
    {
        $event = json_decode($message->body(), true, 512, JSON_THROW_ON_ERROR);

        $this->browserNotifier->publish(
            ['lobby', 'connect-four-' . $event['gameId']],
            $message->name(),
            $message->body()
        );
    }

    private function handleGameResigned(Message $message): void
    {
        $event = json_decode($message->body(), true, 512, JSON_THROW_ON_ERROR);

        $this->browserNotifier->publish(
            ['connect-four-' . $event['gameId']],
            $message->name(),
            $message->body()
        );
    }

    private function handleGameWon(Message $message): void
    {
        $event = json_decode($message->body(), true, 512, JSON_THROW_ON_ERROR);

        $this->browserNotifier->publish(
            ['connect-four-' . $event['gameId']],
            $message->name(),
            $message->body()
        );
    }

    private function handleGameDrawn(Message $message): void
    {
        $event = json_decode($message->body(), true, 512, JSON_THROW_ON_ERROR);

        $this->browserNotifier->publish(
            ['connect-four-' . $event['gameId']],
            $message->name(),
            $message->body()
        );
    }

    private function handlePlayerMoved(Message $message): void
    {
        $event = json_decode($message->body(), true, 512, JSON_THROW_ON_ERROR);

        $this->browserNotifier->publish(
            ['connect-four-' . $event['gameId']],
            $message->name(),
            $message->body()
        );
    }

    private function handleChatAssigned(Message $message): void
    {
        $event = json_decode($message->body(), true, 512, JSON_THROW_ON_ERROR);

        $this->browserNotifier->publish(
            ['connect-four-' . $event['gameId']],
            $message->name(),
            $message->body()
        );
    }

    private function handleMessageWritten(Message $message): void
    {
        $event = new MessageWritten();
        $event->mergeFromString($message->body());

        $this->browserNotifier->publish(
            ['chat-' . $event->getChatId()],
            $message->name(),
            $event->serializeToJsonString()
        );
    }
}
