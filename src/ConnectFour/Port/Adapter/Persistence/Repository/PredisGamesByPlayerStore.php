<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GameByPlayer;
use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GamesByPlayer;
use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GamesByPlayerStore;
use Predis\ClientInterface;

final class PredisGamesByPlayerStore implements GamesByPlayerStore
{
    public function __construct(
        private readonly ClientInterface $predis,
        private readonly string $storageKeyPrefix
    ) {
    }

    public function addToPlayer(string $playerId, string $gameId): void
    {
        $this->predis->zadd(
            $this->storageKeyForPlayer($playerId),
            [$gameId => microtime(true)]
        );
    }

    public function removeFromPlayer(string $playerId, string $gameId): void
    {
        $this->predis->zrem(
            $this->storageKeyForPlayer($playerId),
            $gameId
        );
    }

    public function all(string $playerId): GamesByPlayer
    {
        return new GamesByPlayer(
            array_map(
                static fn($value): GameByPlayer => new GameByPlayer($value),
                $this->predis->zrevrange($this->storageKeyForPlayer($playerId), 0, 100)
            )
        );
    }

    private function storageKeyForPlayer(string $playerId): string
    {
        return $this->storageKeyPrefix . $playerId;
    }
}
