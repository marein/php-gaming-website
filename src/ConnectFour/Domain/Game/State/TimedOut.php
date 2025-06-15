<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\State;

use DateTimeImmutable;
use Gaming\ConnectFour\Domain\Game\Exception\GameFinishedException;
use Gaming\ConnectFour\Domain\Game\GameId;

final class TimedOut implements State
{
    public function join(
        GameId $gameId,
        string $playerId,
        DateTimeImmutable $now = new DateTimeImmutable()
    ): Transition {
        throw new GameFinishedException();
    }

    public function abort(GameId $gameId, string $playerId): Transition
    {
        throw new GameFinishedException();
    }

    public function resign(GameId $gameId, string $playerId): Transition
    {
        throw new GameFinishedException();
    }

    public function move(
        GameId $gameId,
        string $playerId,
        int $column,
        DateTimeImmutable $now = new DateTimeImmutable()
    ): Transition {
        throw new GameFinishedException();
    }

    public function timeout(GameId $gameId, DateTimeImmutable $now = new DateTimeImmutable()): Transition
    {
        throw new GameFinishedException();
    }
}
