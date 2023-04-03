<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Command;

use Gaming\ConnectFour\Domain\Game\Game;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\Games;

final class JoinHandler
{
    private Games $games;

    public function __construct(Games $games)
    {
        $this->games = $games;
    }

    public function __invoke(JoinCommand $command): void
    {
        $this->games->update(
            GameId::fromString($command->gameId()),
            static fn(Game $game) => $game->join($command->playerId())
        );
    }
}
