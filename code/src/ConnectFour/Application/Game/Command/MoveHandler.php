<?php

namespace Gambling\ConnectFour\Application\Game\Command;

use Gambling\ConnectFour\Domain\Game\GameId;
use Gambling\ConnectFour\Domain\Game\Games;

final class MoveHandler
{
    /**
     * @var Games
     */
    private $games;

    /**
     * MoveHandler constructor.
     *
     * @param Games $games
     */
    public function __construct(Games $games)
    {
        $this->games = $games;
    }

    public function __invoke(MoveCommand $command): void
    {
        $game = $this->games->get(GameId::fromString($command->gameId()));

        $game->move($command->playerId(), $command->column());

        $this->games->save($game);
    }
}
