<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Gaming\ConnectFour\Application\Game\Query\Model\Game\Game;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\GameFinder;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\GameStore;
use Gaming\ConnectFour\Domain\Game\GameId;
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
    public function __construct(
        private readonly ClientInterface $predis,
        private readonly string $storageKeyPrefix,
        private readonly GameFinder $fallbackGameFinder
    ) {
    }

    public function find(GameId $gameId): Game
    {
        $serializedGame = $this->predis->get(
            $this->storageKeyPrefix . $gameId
        );

        // If no game is found, use the fallback.
        if (!$serializedGame) {
            return $this->fallbackGameFinder->find($gameId);
        }

        return unserialize($serializedGame);
    }

    public function save(Game $game): void
    {
        $this->predis->set(
            $this->storageKeyPrefix . $game->id(),
            serialize($game)
        );
    }
}
