<?php

namespace Gambling\ConnectFour\Domain\Game\State;

use Gambling\ConnectFour\Domain\Game\Exception\GameException;
use Gambling\ConnectFour\Domain\Game\GameId;

interface State
{
    /**
     * The given player joins the game.
     *
     * @param GameId $gameId
     * @param string $playerId
     *
     * @return Transition
     * @throws GameException
     */
    public function join(GameId $gameId, string $playerId): Transition;

    /**
     * The given player aborts the game.
     *
     * @param GameId $gameId
     * @param string $playerId
     *
     * @return Transition
     * @throws GameException
     */
    public function abort(GameId $gameId, string $playerId): Transition;

    /**
     * The given player makes the move in the given column.
     *
     * @param GameId $gameId
     * @param string $playerId
     * @param int    $column
     *
     * @return Transition
     * @throws GameException
     */
    public function move(GameId $gameId, string $playerId, int $column): Transition;
}
