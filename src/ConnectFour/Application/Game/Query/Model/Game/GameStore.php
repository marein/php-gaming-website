<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\Model\Game;

/**
 * The GameStore allows us to find and save a game.
 *
 * The underlying finder is split into a separate interface
 * because not every implementation can store the query model.
 */
interface GameStore extends GameFinder
{
    /**
     * Queues up the game for persistence. Once queued, it's stored in the database by calling flush.
     *
     * Implementations may choose to flush at any given point in time, such as when the queue becomes too large.
     */
    public function persist(Game $game): void;

    /**
     * Flushes all queued up games to the database.
     */
    public function flush(): void;
}
