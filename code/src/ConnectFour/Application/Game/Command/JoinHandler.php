<?php

namespace Gambling\ConnectFour\Application\Game\Command;

use Gambling\ConnectFour\Domain\Game\GameId;
use Gambling\ConnectFour\Domain\Game\Games;

final class JoinHandler
{
    /**
     * @var Games
     */
    private $games;

    /**
     * JoinHandler constructor.
     *
     * @param Games $games
     */
    public function __construct(Games $games)
    {
        $this->games = $games;
    }

    public function __invoke(JoinCommand $command): void
    {
        $game = $this->games->get(GameId::fromString($command->gameId()));

        $game->join($command->playerId());

        $this->games->save($game);
    }
}
