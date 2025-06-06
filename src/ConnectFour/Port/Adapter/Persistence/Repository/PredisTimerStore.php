<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Predis\ClientInterface;

final class PredisTimerStore
{
    public function __construct(
        private readonly ClientInterface $predis,
        private readonly string $storageKey
    ) {
    }

    /**
     * @param array<string, int> $gameIdToNextPlayerTurnEndsAtMap
     */
    public function add(array $gameIdToNextPlayerTurnEndsAtMap): void
    {
        if (count($gameIdToNextPlayerTurnEndsAtMap) === 0) {
            return;
        }

        $this->predis->zadd($this->storageKey, $gameIdToNextPlayerTurnEndsAtMap);
    }

    /**
     * @param string[] $gameIds
     */
    public function remove(array $gameIds): void
    {
        if (count($gameIds) === 0) {
            return;
        }

        $this->predis->zrem($this->storageKey, ...$gameIds);
    }

    /**
     * @return array<string, int> The key is the game id and the value is the
     *                            time in ms when the player's turn ends.
     */
    public function findGamesToTimeout(int $timeWindowMs, int $limit = 1000): array
    {
        return $this->predis->zrangebyscore(
            $this->storageKey,
            0,
            $timeWindowMs,
            ['limit' => [0, $limit], 'withscores' => true]
        );
    }
}
