<?php

declare(strict_types=1);

namespace Gaming\Chat\Presentation\Messaging;

use Gaming\Chat\Application\Command\InitiateChatCommand;
use Gaming\Common\Bus\Bus;
use Gaming\Common\MessageBroker\Context;
use Gaming\Common\MessageBroker\Message;
use Gaming\Common\MessageBroker\MessageHandler;
use GamingPlatform\Api\Chat\V1\ChatV1Factory;
use GamingPlatform\Api\Chat\V1\InitiateChatResponse;

final class CommandMessageHandler implements MessageHandler
{
    public function __construct(
        private readonly Bus $commandBus
    ) {
    }

    public function handle(Message $message, Context $context): void
    {
        $request = ChatV1Factory::createInitiateChat($message->body());

        $chatId = $this->commandBus->handle(
            new InitiateChatCommand(
                $request->getIdempotencyKey(),
                [...$request->getAuthors()]
            )
        );

        $context->reply(
            new Message(
                'Chat.InitiateChatResponse',
                (new InitiateChatResponse())
                    ->setChatId($chatId)
                    ->setCorrelationId($request->getCorrelationId())
                    ->serializeToString()
            )
        );
    }
}
