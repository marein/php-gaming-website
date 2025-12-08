<?php

declare(strict_types=1);

namespace Gaming\Chat\Presentation\Messaging;

use Gaming\Chat\Application\Command\InitiateChatCommand;
use Gaming\Common\Bus\Bus;
use Gaming\Common\MessageBroker\Context;
use Gaming\Common\MessageBroker\Message;
use Gaming\Common\MessageBroker\MessageHandler;
use GamingPlatform\Api\Chat\V1\ChatV1;

final class CommandMessageHandler implements MessageHandler
{
    public function __construct(
        private readonly Bus $commandBus
    ) {
    }

    public function handle(Message $message, Context $context): void
    {
        $request = ChatV1::createInitiateChat($message->body());

        $chatId = $this->commandBus->handle(
            new InitiateChatCommand(
                $request->getIdempotencyKey(),
                [...$request->getAuthors()]
            )
        );

        $context->reply(
            new Message(
                ChatV1::InitiateChatResponseType,
                ChatV1::createInitiateChatResponse()
                    ->setChatId($chatId)
                    ->setCorrelationId($request->getCorrelationId())
                    ->serializeToString()
            )
        );
    }
}
