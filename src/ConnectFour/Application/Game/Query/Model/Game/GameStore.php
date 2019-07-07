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
     * Store the game.
     *
     * @param Game $game
     */
    public function save(Game $game): void;
}
