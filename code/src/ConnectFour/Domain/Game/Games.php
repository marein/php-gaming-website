<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game;

use Gaming\Common\Domain\Exception\ConcurrencyException;
use Gaming\ConnectFour\Domain\Game\Exception\GameNotFoundException;

interface Games
{
    /**
     * Save the game.
     *
     * @param Game $game
     *
     * @return void
     * @throws ConcurrencyException
     */
    public function save(Game $game): void;

    /**
     * Get a game if exists.
     *
     * @param GameId $gameId
     *
     * @return Game
     * @throws GameNotFoundException
     */
    public function get(GameId $gameId): Game;
}
