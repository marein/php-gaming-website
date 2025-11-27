<?php

declare(strict_types=1);

namespace Gaming\Identity\Application\Bot;

use Gaming\Identity\Application\Bot\Command\RegisterBot\RegisterBot;
use Gaming\Identity\Application\Bot\Command\RegisterBot\RegisterBotResponse;
use Gaming\Identity\Domain\Model\Account\UsernameGenerator;
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
}
