<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GameByPlayer;
use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GamesByPlayer;
use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GamesByPlayerStore;
use Predis\ClientInterface;

final class PredisGamesByPlayerStore implements GamesByPlayerStore
{
    private const STORAGE_KEY_PREFIX = 'games-by-player.';

    private ClientInterface $predis;

    public function __construct(ClientInterface $predis)
    {
        $this->predis = $predis;
    }

    public function addToPlayer(string $playerId, string $gameId): void
    {
        $this->predis->lpush(
            $this->storageKeyForPlayer($playerId),
            [$gameId]
        );
    }

    public function removeFromPlayer(string $playerId, string $gameId): void
    {
        $this->predis->lrem(
            $this->storageKeyForPlayer($playerId),
            0,
            $gameId
        );
    }

    public function all(string $playerId): GamesByPlayer
    {
        return new GamesByPlayer(
            array_map(
                static fn($value): GameByPlayer => new GameByPlayer($value),
                $this->predis->lrange($this->storageKeyForPlayer($playerId), 0, 100)
            )
        );
    }

    private function storageKeyForPlayer(string $playerId): string
    {
        return self::STORAGE_KEY_PREFIX . $playerId;
    }
}
