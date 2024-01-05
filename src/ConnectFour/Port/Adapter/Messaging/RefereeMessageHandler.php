<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Messaging;

use Gaming\Common\Bus\Bus;
use Gaming\Common\MessageBroker\Context;
use Gaming\Common\MessageBroker\Message;
use Gaming\Common\MessageBroker\MessageHandler;
use Gaming\ConnectFour\Application\Game\Command\AssignChatCommand;
use GamingPlatform\Api\Chat\V1\InitiateChat;
use GamingPlatform\Api\Chat\V1\InitiateChatResponse;

final class RefereeMessageHandler implements MessageHandler
{
    public function __construct(
        private readonly Bus $commandBus
    ) {
    }

    public function handle(Message $message, Context $context): void
    {
        match ($message->name()) {
            'Chat.InitiateChatResponse' => $this->handleInitiateChatResponse($message),
            'ConnectFour.PlayerJoined' => $this->handlePlayerJoined($message, $context),
            default => true
        };
    }

    private function handleInitiateChatResponse(Message $message): void
    {
        $response = new InitiateChatResponse();
        $response->mergeFromString($message->body());

        $this->commandBus->handle(
            new AssignChatCommand(
                $response->getCorrelationId(),
                $response->getChatId()
            )
        );
    }

    private function handlePlayerJoined(Message $message, Context $context): void
    {
        $payload = json_decode($message->body(), true, 512, JSON_THROW_ON_ERROR);

        $context->request(
            new Message(
                'Chat.InitiateChat',
                (new InitiateChat())
                    ->setIdempotencyKey('connect-four.' . $payload['gameId'])
                    ->setCorrelationId($payload['gameId'])
                    ->setAuthors([])
                    ->serializeToString()
            )
        );
    }
}
