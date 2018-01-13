<?php

namespace Gambling\ConnectFour\Application\Game\Command;

use Gambling\ConnectFour\Domain\Game\GameId;
use Gambling\ConnectFour\Domain\Game\Games;

final class ResignHandler
{
    /**
     * @var Games
     */
    private $games;

    /**
     * ResignHandler constructor.
     *
     * @param Games $games
     */
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
