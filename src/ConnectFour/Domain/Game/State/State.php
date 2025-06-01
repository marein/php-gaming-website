<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\State;

use DateTimeImmutable;
use Gaming\ConnectFour\Domain\Game\Exception\GameException;
use Gaming\ConnectFour\Domain\Game\GameId;

interface State
{
    /**
     * @throws GameException
     */
    public function join(GameId $gameId, string $playerId): Transition;

    /**
     * @throws GameException
     */
    public function abort(GameId $gameId, string $playerId): Transition;

    /**
     * @throws GameException
     */
    public function resign(GameId $gameId, string $playerId): Transition;

    /**
     * @throws GameException
     */
    public function move(
        GameId $gameId,
        string $playerId,
        int $column,
        DateTimeImmutable $now = new DateTimeImmutable()
    ): Transition;
}
