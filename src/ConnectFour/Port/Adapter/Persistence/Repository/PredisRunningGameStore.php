<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Gaming\ConnectFour\Application\Game\Query\Model\RunningGames\RunningGameStore;
use Predis\ClientInterface;

final class PredisRunningGameStore implements RunningGameStore
{
    public function __construct(
        private readonly ClientInterface $predis,
        private readonly string $storageKey
    ) {
    }

    public function add(string $gameId): void
    {
        $this->predis->sadd($this->storageKey, [$gameId]);
    }

    public function remove(string $gameId): void
    {
        $this->predis->srem($this->storageKey, $gameId);
    }

    public function count(): int
    {
        return $this->predis->scard($this->storageKey);
    }
}
