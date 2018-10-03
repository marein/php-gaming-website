<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Messaging;

use Gaming\Common\Bus\Bus;
use Gaming\Common\MessageBroker\Consumer;
use Gaming\Common\MessageBroker\MessageBroker;
use Gaming\ConnectFour\Application\Game\Command\AssignChatCommand;

final class RefereeConsumer implements Consumer
{
    private const ROUTING_KEY_TO_METHOD = [
        'Chat.ChatInitiated'       => 'handleChatInitiated',
        'ConnectFour.PlayerJoined' => 'handlePlayerJoined'
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
        return 'ConnectFour.Referee';
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
            'Chat.InitiateChat'
        );
    }
}
