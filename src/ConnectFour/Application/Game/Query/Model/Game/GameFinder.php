<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\Model\Game;

use Gaming\ConnectFour\Domain\Game\Exception\GameNotFoundException;
use Gaming\ConnectFour\Domain\Game\GameId;

interface GameFinder
{
    /**
     * @throws GameNotFoundException
     */
    public function find(GameId $gameId): Game;

    /**
     * @param GameId[] $gameIds
     *
     * @return Game[]
     */
    public function findMany(array $gameIds): array;
}
