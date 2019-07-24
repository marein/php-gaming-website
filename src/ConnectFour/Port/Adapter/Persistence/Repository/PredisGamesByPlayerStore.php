<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GameByPlayer;
use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GamesByPlayer;
use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GamesByPlayerStore;
use Predis\Client;

final class PredisGamesByPlayerStore implements GamesByPlayerStore
{
    private const STORAGE_KEY_PREFIX = 'games-by-player.';

    /**
     * @var Client
     */
    private $predis;

    /**
     * PredisGamesByPlayerStore constructor.
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
    public function addToPlayer(string $playerId, string $gameId): void
    {
        $this->predis->lpush(
            $this->storageKeyForPlayer($playerId),
            [$gameId]
        );
    }

    /**
     * @inheritdoc
     */
    public function removeFromPlayer(string $playerId, string $gameId): void
    {
        $this->predis->lrem(
            $this->storageKeyForPlayer($playerId),
            0,
            $gameId
        );
    }

    /**
     * @inheritdoc
     */
    public function all(string $playerId): GamesByPlayer
    {
        return new GamesByPlayer(
            array_map(
                static function ($value) {
                    return new GameByPlayer($value);
                },
                $this->predis->lrange($this->storageKeyForPlayer($playerId), 0, 100)
            )
        );
    }

    /**
     * Returns the storage key for the given player id.
     *
     * @param string $playerId
     *
     * @return string
     */
    private function storageKeyForPlayer(string $playerId): string
    {
        return self::STORAGE_KEY_PREFIX . $playerId;
    }
}
