<?php

declare(strict_types=1);

namespace Gaming\Identity\Application\Bot\Command\RegisterBot;

final class RegisterBotResponse
{
    public function __construct(
        public readonly string $botId,
    ) {
    }
}
