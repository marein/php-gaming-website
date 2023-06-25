<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Messaging;

use Gaming\Common\Bus\Bus;
use Gaming\Common\MessageBroker\Context;
use Gaming\Common\MessageBroker\Message;
use Gaming\Common\MessageBroker\MessageHandler;
use Gaming\ConnectFour\Application\Game\Command\AssignChatCommand;

final class RefereeMessageHandler implements MessageHandler
{
    public function __construct(
        private readonly Bus $commandBus
    ) {
    }

    public function handle(Message $message, Context $context): void
    {
        $payload = json_decode($message->body(), true, 512, JSON_THROW_ON_ERROR);

        match ($message->name()) {
            'Chat.InitiateChatResponse' => $this->handleInitiateChatResponse($payload),
            'ConnectFour.PlayerJoined' => $this->handlePlayerJoined($payload, $context),
            default => true
        };
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleInitiateChatResponse(array $payload): void
    {
        $this->commandBus->handle(
            new AssignChatCommand(
                $payload['correlationId'],
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
                'Chat.InitiateChat',
                json_encode(
                    [
                        'idempotencyKey' => 'connect-four.' . $payload['gameId'],
                        'correlationId' => $payload['gameId'],
                        'authors' => []
                    ],
                    JSON_THROW_ON_ERROR
                )
            )
        );
    }
}
