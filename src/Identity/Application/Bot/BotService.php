<?php

declare(strict_types=1);

namespace Gaming\Identity\Application\Bot;

use Gaming\Identity\Application\Bot\Command\RegisterBot\RegisterBot;
use Gaming\Identity\Application\Bot\Command\RegisterBot\RegisterBotResponse;
use Gaming\Identity\Application\Bot\Query\Bot as BotQueryModel;
use Gaming\Identity\Application\Bot\Query\GetBotByUsername\GetBotByUsername;
use Gaming\Identity\Application\Bot\Query\GetBotByUsername\GetBotByUsernameResponse;
use Gaming\Identity\Domain\Model\Bot\Bot;
use Gaming\Identity\Domain\Model\Bot\Bots;

final class BotService
{
    public function __construct(
        private readonly Bots $bots
    ) {
    }

    public function registerBot(RegisterBot $request): RegisterBotResponse
    {
        $this->bots->save(
            Bot::register(
                $botId = $this->bots->nextIdentity(),
                $request->username
            )
        );

        return new RegisterBotResponse($botId->toString());
    }

    public function getBotByUsername(GetBotByUsername $request): GetBotByUsernameResponse
    {
        if (!($bot = $this->bots->getByUsername($request->username))) {
            return new GetBotByUsernameResponse(null);
        }

        return new GetBotByUsernameResponse(
            new BotQueryModel($bot->id()->toString(), $bot->username())
        );
    }
}
