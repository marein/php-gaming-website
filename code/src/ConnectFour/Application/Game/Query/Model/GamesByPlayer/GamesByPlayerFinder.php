<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer;

interface GamesByPlayerFinder
{
    /**
     * @param string $playerId
     *
     * @return GamesByPlayer
     */
    public function all(string $playerId): GamesByPlayer;
}
