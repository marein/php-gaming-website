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
use GamingPlatform\Api\Identity\V1\Bot as ProtoBot;
use GamingPlatform\Api\Identity\V1\GetBotByUsernameResponse;
use GamingPlatform\Api\Identity\V1\IdentityV1Factory;
use GamingPlatform\Api\Identity\V1\RegisterBotResponse;

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
            'Identity.RegisterBot' => $this->handleRegisterBot($message, $context),
            'Identity.GetBotByUsername' => $this->handleGetBotByUsername($message, $context),
            default => true
        };
    }

    public function handleRegisterBot(Message $message, Context $context): void
    {
        $request = IdentityV1Factory::createRegisterBot($message->body());

        $response = $this->commandBus->handle(
            new RegisterBot($request->getUsername())
        );

        $context->reply(
            new Message(
                'Identity.RegisterBotResponse',
                new RegisterBotResponse()
                    ->setBotId($response->botId)
                    ->serializeToString()
            )
        );
    }

    public function handleGetBotByUsername(Message $message, Context $context): void
    {
        $request = IdentityV1Factory::createGetBotByUsername($message->body());

        $response = $this->queryBus->handle(
            new GetBotByUsername($request->getUsername())
        );

        $context->reply(
            new Message(
                'Identity.GetBotByUsernameResponse',
                match ($response->bot) {
                    null => new GetBotByUsernameResponse()->serializeToString(),
                    default => new GetBotByUsernameResponse()
                        ->setBot($this->castBotToProtoBot($response->bot))
                        ->serializeToString()
                }
            )
        );
    }

    private function castBotToProtoBot(Bot $bot): ProtoBot
    {
        return new ProtoBot()
            ->setBotId($bot->botId)
            ->setUsername($bot->username);
    }
}
