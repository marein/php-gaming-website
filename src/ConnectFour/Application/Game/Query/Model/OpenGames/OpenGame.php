<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\Model\OpenGames;

final class OpenGame
{
    public function __construct(
        public readonly string $gameId,
        public readonly string $playerId
    ) {
    }
}
