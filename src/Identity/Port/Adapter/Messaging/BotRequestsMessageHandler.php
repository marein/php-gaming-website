<?php

declare(strict_types=1);

namespace Gaming\Identity\Port\Adapter\Messaging;

use Gaming\Common\Bus\Bus;
use Gaming\Common\MessageBroker\Context;
use Gaming\Common\MessageBroker\Message;
use Gaming\Common\MessageBroker\MessageHandler;
use Gaming\Identity\Application\Bot\Command\RegisterBot\RegisterBot;
use Gaming\Identity\Application\Bot\Query\Bot;
use Gaming\Identity\Application\Bot\Query\GetBotByUsername\GetBotByUsername;
use GamingPlatform\Api\Identity\V1\Bot as ProtoV1Bot;
use GamingPlatform\Api\Identity\V1\IdentityV1;

final class BotRequestsMessageHandler implements MessageHandler
{
    public function __construct(
        private readonly Bus $commandBus,
        private readonly Bus $queryBus
    ) {
    }

    public function handle(Message $message, Context $context): void
    {
        match ($message->name()) {
            IdentityV1::RegisterBotType => $this->handleRegisterBot($message, $context),
            IdentityV1::GetBotByUsernameType => $this->handleGetBotByUsername($message, $context),
            default => true
        };
    }

    public function handleRegisterBot(Message $message, Context $context): void
    {
        $request = IdentityV1::createRegisterBot($message->body());

        $response = $this->commandBus->handle(
            new RegisterBot($request->getUsername())
        );

        $context->reply(
            new Message(
                IdentityV1::RegisterBotResponseType,
                IdentityV1::createRegisterBotResponse()
                    ->setBotId($response->botId)
                    ->serializeToString()
            )
        );
    }

    public function handleGetBotByUsername(Message $message, Context $context): void
    {
        $request = IdentityV1::createGetBotByUsername($message->body());

        $response = $this->queryBus->handle(
            new GetBotByUsername($request->getUsername())
        );

        $context->reply(
            new Message(
                IdentityV1::GetBotByUsernameResponseType,
                match ($response->bot) {
                    null => IdentityV1::createGetBotByUsernameResponse()->serializeToString(),
                    default => IdentityV1::createGetBotByUsernameResponse()
                        ->setBot($this->castBotToProtoBot($response->bot))
                        ->serializeToString()
                }
            )
        );
    }

    private function castBotToProtoBot(Bot $bot): ProtoV1Bot
    {
        return IdentityV1::createBot()
            ->setBotId($bot->botId)
            ->setUsername($bot->username);
    }
}
