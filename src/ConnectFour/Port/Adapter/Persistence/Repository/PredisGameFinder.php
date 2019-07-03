<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Gaming\ConnectFour\Application\Game\Query\Model\Game\Game;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\GameFinder;
use Gaming\ConnectFour\Port\Adapter\Persistence\Projection\PredisGameProjection;
use Predis\Client;

final class PredisGameFinder implements GameFinder
{
    /**
     * The predis client.
     *
     * @var Client
     */
    private $predis;

    /**
     * If no game is found, this finder uses the fallback.
     *
     * @var GameFinder
     */
    private $fallbackGameFinder;

    /**
     * PredisGameFinder constructor.
     *
     * @param Client     $predis
     * @param GameFinder $fallbackGameFinder
     */
    public function __construct(Client $predis, GameFinder $fallbackGameFinder)
    {
        $this->predis = $predis;
        $this->fallbackGameFinder = $fallbackGameFinder;
    }

    /**
     * @inheritdoc
     */
    public function find(string $gameId): Game
    {
        $serializedGame = $this->predis->get(
            PredisGameProjection::STORAGE_KEY_PREFIX . $gameId
        );

        // If no game is found, use the fallback.
        if (!$serializedGame) {
            return $this->fallbackGameFinder->find($gameId);
        }

        return unserialize($serializedGame);
    }
}
