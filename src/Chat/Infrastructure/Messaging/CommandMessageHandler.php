<?php

declare(strict_types=1);

namespace Gaming\Chat\Infrastructure\Messaging;

use Gaming\Chat\Application\Command\InitiateChatCommand;
use Gaming\Common\Bus\Bus;
use Gaming\Common\MessageBroker\Context;
use Gaming\Common\MessageBroker\Message;
use Gaming\Common\MessageBroker\MessageHandler;

final class CommandMessageHandler implements MessageHandler
{
    public function __construct(
        private readonly Bus $commandBus
    ) {
    }

    public function handle(Message $message, Context $context): void
    {
        $payload = json_decode($message->body(), true, 512, JSON_THROW_ON_ERROR);

        $chatId = $this->commandBus->handle(
            new InitiateChatCommand(
                $payload['idempotencyKey'],
                $payload['authors']
            )
        );
        assert(is_string($chatId));

        $context->reply(
            new Message(
                'Chat.InitiateChatResponse',
                json_encode(
                    [
                        'chatId' => $chatId,
                        'correlationId' => $payload['correlationId']
                    ],
                    JSON_THROW_ON_ERROR
                )
            )
        );
    }
}
