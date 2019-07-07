<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer;

interface GamesByPlayerStore
{
    /**
     * Adds the game to the player's list.
     *
     * This operation is idempotent.
     *
     * @param string $playerId
     */
    public function addToPlayer(string $playerId, string $gameId): void;

    /**
     * Removes the game from the player's list.
     *
     * This operation is idempotent.
     *
     * @param string $playerId
     */
    public function removeFromPlayer(string $playerId, string $gameId): void;

    /**
     * Find all games for the given player.
     *
     * @param string $playerId
     *
     * @return GamesByPlayer
     */
    public function all(string $playerId): GamesByPlayer;
}
