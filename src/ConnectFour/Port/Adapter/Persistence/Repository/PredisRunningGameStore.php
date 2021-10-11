<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Gaming\ConnectFour\Application\Game\Query\Model\RunningGames\RunningGameStore;
use Predis\ClientInterface;

final class PredisRunningGameStore implements RunningGameStore
{
    private const STORAGE_KEY = 'running-games';

    private ClientInterface $predis;

    public function __construct(ClientInterface $predis)
    {
        $this->predis = $predis;
    }

    public function add(string $gameId): void
    {
        $this->predis->sadd(self::STORAGE_KEY, [$gameId]);
    }

    public function remove(string $gameId): void
    {
        $this->predis->srem(self::STORAGE_KEY, $gameId);
    }

    public function count(): int
    {
        return $this->predis->scard(self::STORAGE_KEY);
    }
}
