<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\Messaging;

use Gaming\Common\MessageBroker\Model\Consumer\Consumer;
use Gaming\Common\MessageBroker\Model\Consumer\Name;
use Gaming\Common\MessageBroker\Model\Message\Message;
use Gaming\Common\MessageBroker\Model\Subscription\SpecificMessage;
use Gaming\Common\MessageBroker\Model\Subscription\WholeDomain;
use Gaming\WebInterface\Application\BrowserNotifier;

final class PublishRabbitMqEventsToNchanConsumer implements Consumer
{
    private const ROUTING_KEY_TO_METHOD = [
        'ConnectFour.GameOpened' => 'handleGameOpened',
        'ConnectFour.GameAborted' => 'handleGameAborted',
        'ConnectFour.GameResigned' => 'handleGameResigned',
        'ConnectFour.GameWon' => 'handleGameWon',
        'ConnectFour.GameDrawn' => 'handleGameDrawn',
        'ConnectFour.PlayerMoved' => 'handlePlayerMoved',
        'ConnectFour.PlayerJoined' => 'handlePlayerJoined',
        'ConnectFour.ChatAssigned' => 'handleChatAssigned',
        'Chat.MessageWritten' => 'handleMessageWritten'
    ];

    private BrowserNotifier $browserNotifier;

    public function __construct(BrowserNotifier $browserNotifier)
    {
        $this->browserNotifier = $browserNotifier;
    }

    public function handle(Message $message): void
    {
        $name = (string)$message->name();

        $method = self::ROUTING_KEY_TO_METHOD[$name];
        $payload = array_merge(
            json_decode($message->body(), true, 512, JSON_THROW_ON_ERROR),
            ['eventName' => $name]
        );

        $this->$method($payload);
    }

    public function subscriptions(): array
    {
        return [
            new WholeDomain('ConnectFour'),
            new SpecificMessage('Chat', 'MessageWritten')
        ];
    }

    public function name(): Name
    {
        return new Name('WebInterface', 'BrowserNotification');
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleGameOpened(array $payload): void
    {
        $this->browserNotifier->publish(
            '/pub?id=lobby',
            json_encode($payload, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleGameAborted(array $payload): void
    {
        $this->browserNotifier->publish(
            '/pub?id=lobby',
            json_encode($payload, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleGameResigned(array $payload): void
    {
        $this->browserNotifier->publish(
            '/pub?id=game-' . $payload['gameId'],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handlePlayerJoined(array $payload): void
    {
        $payloadAsJson = json_encode($payload, JSON_THROW_ON_ERROR);

        $this->browserNotifier->publish(
            '/pub?id=lobby',
            $payloadAsJson
        );

        $this->browserNotifier->publish(
            '/pub?id=game-' . $payload['gameId'],
            $payloadAsJson
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleGameWon(array $payload): void
    {
        $this->browserNotifier->publish(
            '/pub?id=game-' . $payload['gameId'],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleGameDrawn(array $payload): void
    {
        $this->browserNotifier->publish(
            '/pub?id=game-' . $payload['gameId'],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handlePlayerMoved(array $payload): void
    {
        $this->browserNotifier->publish(
            '/pub?id=game-' . $payload['gameId'],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleChatAssigned(array $payload): void
    {
        $this->browserNotifier->publish(
            '/pub?id=game-' . $payload['gameId'],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleMessageWritten(array $payload): void
    {
        $this->browserNotifier->publish(
            '/pub?id=game-' . $payload['ownerId'],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );
    }
}
