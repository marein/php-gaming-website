<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Command;

use Gaming\ConnectFour\Domain\Game\Exception\GameFinishedException;
use Gaming\ConnectFour\Domain\Game\Game;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\Games;

final class TimeoutHandler
{
    public function __construct(
        public readonly Games $games
    ) {
    }

    public function __invoke(TimeoutCommand $command): void
    {
        try {
            $this->games->update(
                GameId::fromString($command->gameId),
                static fn(Game $game) => $game->timeout()
            );
        } catch (GameFinishedException) {
            // This can happen if the timeout command is processed after the game has been finished.
        }
    }
}
