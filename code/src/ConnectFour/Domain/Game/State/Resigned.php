<?php

namespace Gambling\ConnectFour\Domain\Game\State;

use Gambling\ConnectFour\Domain\Game\Exception\GameFinishedException;
use Gambling\ConnectFour\Domain\Game\GameId;

final class Resigned implements State
{
    /**
     * @inheritdoc
     */
    public function join(GameId $gameId, string $playerId): Transition
    {
        throw new GameFinishedException();
    }

    /**
     * @inheritdoc
     */
    public function abort(GameId $gameId, string $playerId): Transition
    {
        throw new GameFinishedException();
    }

    /**
     * @inheritdoc
     */
    public function resign(GameId $gameId, string $playerId): Transition
    {
        throw new GameFinishedException();
    }

    /**
     * @inheritdoc
     */
    public function move(GameId $gameId, string $playerId, int $column): Transition
    {
        throw new GameFinishedException();
    }
}
