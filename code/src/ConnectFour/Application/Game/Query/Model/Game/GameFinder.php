<?php

namespace Gambling\ConnectFour\Application\Game\Query\Model\Game;

interface GameFinder
{
    /**
     * @param $gameId
     *
     * @return Game|null
     */
    public function find(string $gameId): ?Game;
}
