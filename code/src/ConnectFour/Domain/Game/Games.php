<?php

namespace Gambling\ConnectFour\Domain\Game;

use Gambling\Common\Domain\Exception\ConcurrencyException;

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
     * @return Game|null
     */
    public function get(GameId $gameId): ?Game;
}
