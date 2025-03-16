<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Command;

use Gaming\ConnectFour\Domain\Game\Board\Size;
use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\Configuration;
use Gaming\ConnectFour\Domain\Game\Game;
use Gaming\ConnectFour\Domain\Game\Games;
use Gaming\ConnectFour\Domain\Game\WinningRule\WinningRules;

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
            $this->games->nextIdentity(),
            new Configuration(
                new Size($command->width, $command->height),
                WinningRules::standard(),
                Stone::tryFrom($command->stone)
            ),
            $command->playerId
        );

        $this->games->add($game);

        return $game->id()->toString();
    }
}
