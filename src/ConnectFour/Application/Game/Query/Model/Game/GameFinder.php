<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\Model\Game;

use Gaming\ConnectFour\Application\Game\Query\Exception\GameNotFoundException;

interface GameFinder
{
    /**
     * @throws GameNotFoundException
     */
    public function find(string $gameId): Game;
}
