<?php

declare(strict_types=1);

namespace Gaming\Identity\Application\Bot\Query;

final class Bot
{
    public function __construct(
        public readonly string $botId,
        public readonly string $username
    ) {
    }
}
