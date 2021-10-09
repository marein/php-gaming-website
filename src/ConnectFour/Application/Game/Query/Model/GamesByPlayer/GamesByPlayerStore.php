<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer;

interface GamesByPlayerStore
{
    /**
     * This operation is idempotent.
     */
    public function addToPlayer(string $playerId, string $gameId): void;

    /**
     * This operation is idempotent.
     */
    public function removeFromPlayer(string $playerId, string $gameId): void;

    public function all(string $playerId): GamesByPlayer;
}
