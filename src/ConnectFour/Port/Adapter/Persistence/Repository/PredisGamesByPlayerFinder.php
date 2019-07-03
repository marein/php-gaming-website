<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GameByPlayer;
use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GamesByPlayer;
use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GamesByPlayerFinder;
use Gaming\ConnectFour\Port\Adapter\Persistence\Projection\PredisGamesByPlayerProjection;
use Predis\Client;

final class PredisGamesByPlayerFinder implements GamesByPlayerFinder
{
    /**
     * @var Client
     */
    private $predis;

    /**
     * PredisGamesByPlayerFinder constructor.
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
    public function all(string $playerId): GamesByPlayer
    {
        $key = PredisGamesByPlayerProjection::STORAGE_KEY_PREFIX . $playerId;

        return new GamesByPlayer(
            array_map(function ($value) {
                return new GameByPlayer($value);
            }, $this->predis->lrange($key, 0, 100))
        );
    }
}
