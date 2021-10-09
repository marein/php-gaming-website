<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Command;

use Gaming\ConnectFour\Domain\Game\Configuration;
use Gaming\ConnectFour\Domain\Game\Game;
use Gaming\ConnectFour\Domain\Game\Games;

final class OpenHandler
{
    private Games $games;

    public function __construct(Games $games)
    {
        $this->games = $games;
    }

    public function __invoke(OpenCommand $command): string
    {
        $game = Game::open(
            Configuration::common(),
            $command->playerId()
        );

        $this->games->save($game);

        return $game->id()->toString();
    }
}
