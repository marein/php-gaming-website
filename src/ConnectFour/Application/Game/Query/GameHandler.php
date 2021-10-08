<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query;

use Gaming\ConnectFour\Application\Game\Query\Exception\GameNotFoundException;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\Game;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\GameFinder;

final class GameHandler
{
    /**
     * @var GameFinder
     */
    private GameFinder $gameFinder;

    /**
     * GameHandler constructor.
     *
     * @param GameFinder $gameFinder
     */
    public function __construct(GameFinder $gameFinder)
    {
        $this->gameFinder = $gameFinder;
    }

    /**
     * Query the game finder.
     *
     * @param GameQuery $query
     *
     * @return Game
     * @throws GameNotFoundException
     */
    public function __invoke(GameQuery $query): Game
    {
        return $this->gameFinder->find(
            $query->gameId()
        );
    }
}
