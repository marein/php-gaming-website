<?php

namespace Gambling\ConnectFour\Application\Game\Command;

use Gambling\ConnectFour\Domain\Game\Configuration;
use Gambling\ConnectFour\Domain\Game\Game;
use Gambling\ConnectFour\Domain\Game\Games;

final class OpenHandler
{
    /**
     * @var Games
     */
    private $games;

    /**
     * OpenHandler constructor.
     *
     * @param Games $games
     */
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
