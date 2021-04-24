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
        'ConnectFour.GameOpened'   => 'handleGameOpened',
        'ConnectFour.GameAborted'  => 'handleGameAborted',
        'ConnectFour.GameResigned' => 'handleGameResigned',
        'ConnectFour.GameWon'      => 'handleGameWon',
        'ConnectFour.GameDrawn'    => 'handleGameDrawn',
        'ConnectFour.PlayerMoved'  => 'handlePlayerMoved',
        'ConnectFour.PlayerJoined' => 'handlePlayerJoined',
        'ConnectFour.ChatAssigned' => 'handleChatAssigned',
        'Chat.MessageWritten'      => 'handleMessageWritten'
    ];

    /**
     * @var BrowserNotifier
     */
    private BrowserNotifier $browserNotifier;

    /**
     * PublishRabbitMqEventsToNchanConsumer constructor.
     *
     * @param BrowserNotifier $browserNotifier
     */
    public function __construct(BrowserNotifier $browserNotifier)
    {
        $this->browserNotifier = $browserNotifier;
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function subscriptions(): array
    {
        return [
            new WholeDomain('ConnectFour'),
            new SpecificMessage('Chat', 'MessageWritten')
        ];
    }

    /**
     * @inheritdoc
     */
    public function name(): Name
    {
        return new Name('WebInterface', 'BrowserNotification');
    }

    /**
     * Publish game opened.
     *
     * @param array $payload
     */
    private function handleGameOpened(array $payload): void
    {
        $this->browserNotifier->publish(
            '/pub?id=lobby',
            json_encode($payload, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * Publish game aborted.
     *
     * @param array $payload
     */
    private function handleGameAborted(array $payload): void
    {
        $this->browserNotifier->publish(
            '/pub?id=lobby',
            json_encode($payload, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * Publish game resigned.
     *
     * @param array $payload
     */
    private function handleGameResigned(array $payload): void
    {
        $this->browserNotifier->publish(
            '/pub?id=game-' . $payload['gameId'],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * Publish player joined.
     *
     * @param array $payload
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
     * Publish game won.
     *
     * @param array $payload
     */
    private function handleGameWon(array $payload): void
    {
        $this->browserNotifier->publish(
            '/pub?id=game-' . $payload['gameId'],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * Publish game drawn.
     *
     * @param array $payload
     */
    private function handleGameDrawn(array $payload): void
    {
        $this->browserNotifier->publish(
            '/pub?id=game-' . $payload['gameId'],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * Publish player moved.
     *
     * @param array $payload
     */
    private function handlePlayerMoved(array $payload): void
    {
        $this->browserNotifier->publish(
            '/pub?id=game-' . $payload['gameId'],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * Publish chat assigned.
     *
     * @param array $payload
     */
    private function handleChatAssigned(array $payload): void
    {
        $this->browserNotifier->publish(
            '/pub?id=game-' . $payload['gameId'],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * Publish message written.
     *
     * @param array $payload
     */
    private function handleMessageWritten(array $payload): void
    {
        $this->browserNotifier->publish(
            '/pub?id=game-' . $payload['ownerId'],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );
    }
}
