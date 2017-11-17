<?php

namespace Gambling\ConnectFour\Domain\Game\State;

use Gambling\ConnectFour\Domain\Game\Board\Board;
use Gambling\ConnectFour\Domain\Game\Exception\GameFinishedException;
use Gambling\ConnectFour\Domain\Game\Game;

final class Aborted implements State
{
    /**
     * @inheritdoc
     */
    public function join(Game $game, string $playerId): void
    {
        throw new GameFinishedException();
    }

    /**
     * @inheritdoc
     */
    public function abort(Game $game, string $playerId): void
    {
        throw new GameFinishedException();
    }

    /**
     * @inheritdoc
     */
    public function move(Game $game, string $playerId, int $column): void
    {
        throw new GameFinishedException();
    }

    /**
     * @inheritdoc
     */
    public function board(): Board
    {
        throw new GameFinishedException();
    }
}
