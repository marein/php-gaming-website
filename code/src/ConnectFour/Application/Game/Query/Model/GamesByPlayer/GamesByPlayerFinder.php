<?php

namespace Gambling\ConnectFour\Application\Game\Query\Model\GamesByPlayer;

interface GamesByPlayerFinder
{
    /**
     * @param string $playerId
     *
     * @return GamesByPlayer
     */
    public function all(string $playerId): GamesByPlayer;
}
