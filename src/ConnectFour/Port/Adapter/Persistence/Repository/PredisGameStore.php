<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Gaming\ConnectFour\Application\Game\Query\Model\Game\Game;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\GameFinder;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\GameStore;
use Predis\ClientInterface;

/**
 * This class stores the game with serialize() and retrieves it with unserialize().
 * A serialized game is around ~13 kilobyte in size. This is huge, compared to json with only ~1.3 kilobyte.
 * If this becomes a bottleneck, we can serialize it to json at a later stage.
 *
 * If the model changes, the query model can be completely recreated, so we're not afraid to use serialize() here.
 */
final class PredisGameStore implements GameStore
{
    private const STORAGE_KEY_PREFIX = 'game.';

    /**
     * The predis client.
     *
     * @var ClientInterface
     */
    private ClientInterface $predis;

    /**
     * If no game is found, this store uses this fallback.
     *
     * @var GameFinder
     */
    private GameFinder $fallbackGameFinder;

    /**
     * PredisGameStore constructor.
     *
     * @param ClientInterface $predis
     * @param GameFinder      $fallbackGameFinder
     */
    public function __construct(ClientInterface $predis, GameFinder $fallbackGameFinder)
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
            self::STORAGE_KEY_PREFIX . $gameId
        );

        // If no game is found, use the fallback.
        if (!$serializedGame) {
            return $this->fallbackGameFinder->find($gameId);
        }

        return unserialize($serializedGame);
    }

    /**
     * @inheritdoc
     */
    public function save(Game $game): void
    {
        $this->predis->set(
            self::STORAGE_KEY_PREFIX . $game->id(),
            serialize($game)
        );
    }
}
