<?php

namespace Gambling\ConnectFour\Domain\Game\State;

use Gambling\ConnectFour\Domain\Game\Board\Board;
use Gambling\ConnectFour\Domain\Game\Exception\GameException;
use Gambling\ConnectFour\Domain\Game\Game;

interface State
{
    /**
     * The given player joins the game.
     *
     * @param Game   $game
     * @param string $playerId
     *
     * @throws GameException
     */
    public function join(Game $game, string $playerId): void;

    /**
     * The given player aborts the game.
     *
     * @param Game   $game
     * @param string $playerId
     *
     * @throws GameException
     */
    public function abort(Game $game, string $playerId): void;

    /**
     * The given player makes the move in the given column.
     *
     * @param Game   $game
     * @param string $playerId
     * @param int    $column
     *
     * @throws GameException
     */
    public function move(Game $game, string $playerId, int $column): void;

    /**
     * Get the board.
     *
     * @return Board
     *
     * @throws GameException
     */
    public function board(): Board;
}
