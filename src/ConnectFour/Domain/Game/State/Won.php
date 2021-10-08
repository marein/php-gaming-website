<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\State;

use Gaming\ConnectFour\Domain\Game\Exception\GameFinishedException;
use Gaming\ConnectFour\Domain\Game\GameId;

final class Won implements State
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
