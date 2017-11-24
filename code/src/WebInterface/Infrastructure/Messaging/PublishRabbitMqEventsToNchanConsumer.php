<?php

namespace Gambling\WebInterface\Infrastructure\Messaging;

use Gambling\Common\MessageBroker\Consumer;
use Gambling\WebInterface\Application\BrowserNotifier;

final class PublishRabbitMqEventsToNchanConsumer implements Consumer
{
    private const ROUTING_KEY_TO_METHOD = [
        'connect-four.game-opened'   => 'handleGameOpened',
        'connect-four.game-aborted'  => 'handleGameAborted',
        'connect-four.game-won'      => 'handleGameWon',
        'connect-four.game-drawn'    => 'handleGameDrawn',
        'connect-four.player-moved'  => 'handlePlayerMoved',
        'connect-four.player-joined' => 'handlePlayerJoined',
        'connect-four.chat-assigned' => 'handleChatAssigned',
        'chat.message-written'       => 'handleMessageWritten'
    ];

    /**
     * @var BrowserNotifier
     */
    private $browserNotifier;

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
    public function handle(string $body, string $routingKey): void
    {
        $method = self::ROUTING_KEY_TO_METHOD[$routingKey];
        $payload = array_merge(
            json_decode($body, true),
            ['eventName' => $routingKey]
        );

        $this->$method($payload);
    }

    /**
     * @inheritdoc
     */
    public function routingKeys(): array
    {
        return ['connect-four.#', 'chat.message-written'];
    }

    /**
     * @inheritdoc
     */
    public function queueName(): string
    {
        return 'web-interface.browser-notification';
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
            json_encode($payload)
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
            json_encode($payload)
        );
    }

    /**
     * Publish player joined.
     *
     * @param array $payload
     */
    private function handlePlayerJoined(array $payload): void
    {
        $this->browserNotifier->publish(
            '/pub?id=lobby',
            json_encode($payload)
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
            json_encode($payload)
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
            json_encode($payload)
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
            json_encode($payload)
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
            json_encode($payload)
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
            json_encode($payload)
        );
    }
}
