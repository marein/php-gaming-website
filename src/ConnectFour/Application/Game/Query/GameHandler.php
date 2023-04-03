<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query;

use Gaming\ConnectFour\Application\Game\Query\Model\Game\Game;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\GameFinder;
use Gaming\ConnectFour\Domain\Game\Exception\GameNotFoundException;
use Gaming\ConnectFour\Domain\Game\GameId;

final class GameHandler
{
    private GameFinder $gameFinder;

    public function __construct(GameFinder $gameFinder)
    {
        $this->gameFinder = $gameFinder;
    }

    /**
     * @throws GameNotFoundException
     */
    public function __invoke(GameQuery $query): Game
    {
        return $this->gameFinder->find(GameId::fromString($query->gameId()));
    }
}
