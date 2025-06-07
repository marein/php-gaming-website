<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game;

use Closure;
use Gaming\Common\Domain\Exception\ConcurrencyException;
use Gaming\ConnectFour\Domain\Game\Exception\GameException;
use Gaming\ConnectFour\Domain\Game\Exception\GameNotFoundException;

/**
 * This repository is very persistence oriented to highlight the transactional scope (technical-wise).
 * Games can be sharded across multiple physical databases, hence the transactional scope is applied
 * in the repository and not in the application layer.
 */
interface Games
{
    public function nextIdentity(): GameId;

    public function add(Game $game): void;

    /**
     * @params Closure(Game): void $operation
     *
     * @throws ConcurrencyException
     * @throws GameException
     */
    public function update(GameId $gameId, Closure $operation): void;
}
