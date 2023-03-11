<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game;

use Closure;
use Gaming\Common\Domain\DomainEvent;
use Gaming\Common\Domain\Exception\ConcurrencyException;
use Gaming\ConnectFour\Domain\Game\Exception\GameNotFoundException;

interface Games
{
    public function nextIdentity(): GameId;

    public function add(Game $game): void;

    /**
     * @params Closure(Game): void $operation
     *
     * @throws ConcurrencyException
     * @throws GameNotFoundException
     */
    public function update(GameId $gameId, Closure $operation): void;

    /**
     * @return DomainEvent[]
     */
    public function eventsFor(GameId $gameId): array;
}
