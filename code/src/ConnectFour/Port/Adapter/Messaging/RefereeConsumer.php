<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Messaging;

use Gaming\Common\Bus\Bus;
use Gaming\Common\MessageBroker\MessageBroker;
use Gaming\Common\MessageBroker\Model\Consumer\Consumer;
use Gaming\Common\MessageBroker\Model\Consumer\Name;
use Gaming\Common\MessageBroker\Model\Message\Message;
use Gaming\Common\MessageBroker\Model\Message\Name as MessageName;
use Gaming\Common\MessageBroker\Model\Subscription\SpecificMessage;
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
    public function handle(Message $message): void
    {
        $method = self::ROUTING_KEY_TO_METHOD[(string)$message->name()];
        $payload = json_decode($message->body(), true);

        $this->$method($payload);
    }

    /**
     * @inheritdoc
     */
    public function subscriptions(): array
    {
        return [
            new SpecificMessage('Chat', 'ChatInitiated'),
            new SpecificMessage('ConnectFour', 'PlayerJoined')
        ];
    }

    /**
     * @inheritdoc
     */
    public function name(): Name
    {
        return new Name('ConnectFour', 'Referee');
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
            new Message(
                new MessageName('Chat', 'InitiateChat'),
                json_encode([
                    'ownerId' => $payload['gameId'],
                    'authors' => []
                ])
            )
        );
    }
}
