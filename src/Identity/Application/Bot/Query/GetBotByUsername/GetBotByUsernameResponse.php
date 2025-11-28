<?php

declare(strict_types=1);

namespace Gaming\Identity\Application\Bot\Query\GetBotByUsername;

use Gaming\Identity\Application\Bot\Query\Bot;

final class GetBotByUsernameResponse
{
    public function __construct(
        public readonly ?Bot $bot,
    ) {
    }
}
