<?php

declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\Bot;

use Gaming\Identity\Domain\Model\Account\Account;
use Gaming\Identity\Domain\Model\Account\AccountId;
use Gaming\Identity\Domain\Model\Bot\Event\BotRegistered;

class Bot extends Account
{
    public static function register(AccountId $botId, string $username): self
    {
        $bot = new self($botId, $username);

        $bot->record(new BotRegistered($botId, $username));

        return $bot;
    }
}
