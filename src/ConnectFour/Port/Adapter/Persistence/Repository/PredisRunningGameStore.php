<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Gaming\ConnectFour\Application\Game\Query\Model\RunningGames\RunningGameStore;
use Predis\Client;

final class PredisRunningGameStore implements RunningGameStore
{
    private const STORAGE_KEY = 'running-games';

    /**
     * @var Client
     */
    private $predis;

    /**
     * PredisRunningGameStore constructor.
     *
     * @param Client $predis
     */
    public function __construct(Client $predis)
    {
        $this->predis = $predis;
    }

    /**
     * @inheritdoc
     */
    public function add(string $gameId): void
    {
        $this->predis->sadd(self::STORAGE_KEY, [$gameId]);
    }

    /**
     * @inheritdoc
     */
    public function remove(string $gameId): void
    {
        $this->predis->srem(self::STORAGE_KEY, $gameId);
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return (int)$this->predis->scard(self::STORAGE_KEY);
    }
}
