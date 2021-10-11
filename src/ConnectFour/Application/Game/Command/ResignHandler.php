<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Command;

use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\Games;

final class ResignHandler
{
    private Games $games;

    public function __construct(Games $games)
    {
        $this->games = $games;
    }

    public function __invoke(ResignCommand $command): void
    {
        $game = $this->games->get(GameId::fromString($command->gameId()));

        $game->resign($command->playerId());

        $this->games->save($game);
    }
}
