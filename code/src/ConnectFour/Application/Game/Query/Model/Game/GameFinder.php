<?php

namespace Gambling\ConnectFour\Application\Game\Query\Model\Game;

use Gambling\ConnectFour\Application\Game\Query\Exception\GameNotFoundException;

interface GameFinder
{
    /**
     * Find the game with the given id.
     *
     * @param string $gameId
     *
     * @return Game
     * @throws GameNotFoundException
     */
    public function find(string $gameId): Game;
}
