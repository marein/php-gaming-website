<?php

declare(strict_types=1);

namespace Gaming\Chat\Presentation\Messaging;

use Gaming\Chat\Application\Command\InitiateChatCommand;
use Gaming\Chat\Application\Command\WriteMessageCommand;
use Gaming\Common\Bus\Bus;
use Gaming\Common\MessageBroker\Context;
use Gaming\Common\MessageBroker\Message;
use Gaming\Common\MessageBroker\MessageHandler;
use GamingPlatform\Api\Chat\V1\ChatV1;

final class RpcMessageHandler implements MessageHandler
{
    public function __construct(
        private readonly Bus $commandBus
    ) {
    }

    public function handle(Message $message, Context $context): void
    {
        match ($message->name()) {
            ChatV1::InitiateChatType => $this->handleInitiateChat($message, $context),
            ChatV1::WriteMessageType => $this->handleWriteMessage($message, $context),
            default => true
        };
    }

    private function handleInitiateChat(Message $message, Context $context): void
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

    private function handleWriteMessage(Message $message, Context $context): void
    {
        $request = ChatV1::createWriteMessage($message->body());

        $this->commandBus->handle(
            new WriteMessageCommand(
                $request->getChatId(),
                $request->getAuthorId(),
                $request->getMessage(),
                $request->getIdempotencyKey() ?: null
            )
        );

        $context->reply(
            new Message(
                ChatV1::WriteMessageResponseType,
                ChatV1::createWriteMessageResponse()
                    ->serializeToString()
            )
        );
    }
}
