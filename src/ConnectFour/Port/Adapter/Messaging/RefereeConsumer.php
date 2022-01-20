<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Messaging;

use Gaming\Common\Bus\Bus;
use Gaming\Common\MessageBroker\Model\Consumer\Consumer;
use Gaming\Common\MessageBroker\Model\Consumer\Name;
use Gaming\Common\MessageBroker\Model\Context\Context;
use Gaming\Common\MessageBroker\Model\Message\Message;
use Gaming\Common\MessageBroker\Model\Message\Name as MessageName;
use Gaming\Common\MessageBroker\Model\Subscription\SpecificMessage;
use Gaming\ConnectFour\Application\Game\Command\AssignChatCommand;

final class RefereeConsumer implements Consumer
{
    private Bus $commandBus;

    public function __construct(Bus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function handle(Message $message, Context $context): void
    {
        $payload = json_decode($message->body(), true, 512, JSON_THROW_ON_ERROR);

        match ((string)$message->name()) {
            'Chat.InitiateChatResponse' => $this->handleInitiateChatResponse($payload),
            'ConnectFour.PlayerJoined' => $this->handlePlayerJoined($payload, $context),
            default => true
        };
    }

    public function subscriptions(): array
    {
        return [
            new SpecificMessage('ConnectFour', 'PlayerJoined')
        ];
    }

    public function name(): Name
    {
        return new Name('ConnectFour', 'Referee');
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleInitiateChatResponse(array $payload): void
    {
        $this->commandBus->handle(
            new AssignChatCommand(
                $payload['ownerId'],
                $payload['chatId']
            )
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handlePlayerJoined(array $payload, Context $context): void
    {
        $context->request(
            new Message(
                new MessageName('Chat', 'InitiateChat'),
                json_encode(
                    [
                        'ownerId' => $payload['gameId'],
                        'authors' => []
                    ],
                    JSON_THROW_ON_ERROR
                )
            )
        );
    }
}
