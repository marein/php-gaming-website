<?php

namespace Gambling\ConnectFour\Port\Adapter\Messaging;

use Gambling\Common\Bus\Bus;
use Gambling\Common\MessageBroker\Consumer;
use Gambling\Common\MessageBroker\MessageBroker;
use Gambling\ConnectFour\Application\Game\Command\AssignChatCommand;

final class RefereeConsumer implements Consumer
{
    private const ROUTING_KEY_TO_METHOD = [
        'chat.chat-initiated'        => 'handleChatInitiated',
        'connect-four.player-joined' => 'handlePlayerJoined'
    ];

    /**
     * @var Bus
     */
    private $commandBus;

    /**
     * @var MessageBroker
     */
    private $messageBroker;

    /**
     * RefereeConsumer constructor.
     *
     * @param Bus           $commandBus
     * @param MessageBroker $messageBroker
     */
    public function __construct(Bus $commandBus, MessageBroker $messageBroker)
    {
        $this->commandBus = $commandBus;
        $this->messageBroker = $messageBroker;
    }

    /**
     * @inheritdoc
     */
    public function handle(string $body, string $routingKey): void
    {
        $method = self::ROUTING_KEY_TO_METHOD[$routingKey];
        $payload = json_decode($body, true);

        $this->$method($payload);
    }

    /**
     * @inheritdoc
     */
    public function routingKeys(): array
    {
        return array_keys(self::ROUTING_KEY_TO_METHOD);
    }

    /**
     * @inheritdoc
     */
    public function queueName(): string
    {
        return 'connect-four.referee';
    }

    /**
     * Assign chat to game.
     *
     * @param array $payload
     */
    private function handleChatInitiated(array $payload): void
    {
        $this->commandBus->handle(
            new AssignChatCommand(
                $payload['ownerId'],
                $payload['chatId']
            )
        );
    }

    /**
     * Publish initiate chat command to other context.
     *
     * @param array $payload
     */
    private function handlePlayerJoined(array $payload): void
    {
        $this->messageBroker->publish(
            json_encode([
                'ownerId' => $payload['gameId'],
                'authors' => []
            ]),
            'chat.initiate-chat'
        );
    }
}
