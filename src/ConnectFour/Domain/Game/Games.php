<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game;

use Gaming\Common\Domain\Exception\ConcurrencyException;
use Gaming\ConnectFour\Domain\Game\Exception\GameNotFoundException;

interface Games
{
    public function nextIdentity(): GameId;

    /**
     * @throws ConcurrencyException
     */
    public function save(Game $game): void;

    /**
     * @throws GameNotFoundException
     */
    public function get(GameId $gameId): Game;
}
