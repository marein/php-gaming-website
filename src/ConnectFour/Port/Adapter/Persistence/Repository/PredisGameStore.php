<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Gaming\Common\Normalizer\Normalizer;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\Game;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\GameFinder;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\GameStore;
use Gaming\ConnectFour\Domain\Game\GameId;
use Predis\Client;
use Predis\ClientContextInterface;

final class PredisGameStore implements GameStore
{
    /**
     * @var array<string, Game>
     */
    private array $pendingGames = [];

    public function __construct(
        private readonly Client $predis,
        private readonly string $storageKeyPrefix,
        private readonly Normalizer $normalizer,
        private readonly GameFinder $fallbackGameFinder,
        private readonly int $maxNumberOfPendingGamesBeforeFlush = 32
    ) {
    }

    public function find(GameId $gameId): Game
    {
        $storedGame = $this->predis->get($this->storageKeyPrefix . $gameId);

        return $storedGame ? $this->deserializeGame($storedGame) : $this->fallbackGameFinder->find($gameId);
    }

    public function findMany(array $gameIds): array
    {
        if (count($gameIds) === 0) {
            return [];
        }

        return array_map(
            fn(?string $storedGame, GameId $gameId): Game => $storedGame !== null
                ? $this->deserializeGame($storedGame)
                : $this->fallbackGameFinder->find($gameId),
            $this->predis->mget(
                array_map(fn(GameId $gameId): string => $this->storageKeyPrefix . $gameId, $gameIds)
            ),
            $gameIds
        );
    }

    public function persist(Game $game): void
    {
        $this->pendingGames[$game->id()] = $game;

        if (count($this->pendingGames) === $this->maxNumberOfPendingGamesBeforeFlush) {
            $this->flush();
        }
    }

    public function flush(): void
    {
        $this->predis->pipeline(function (ClientContextInterface $pipeline): void {
            foreach (array_splice($this->pendingGames, 0) as $game) {
                $pipeline->set(
                    $this->storageKeyPrefix . $game->id(),
                    $this->serializeGame($game)
                );
            }
        });
    }

    private function serializeGame(Game $game): string
    {
        return json_encode($this->normalizer->normalize($game, Game::class), JSON_THROW_ON_ERROR);
    }

    private function deserializeGame(string $game): Game
    {
        return $this->normalizer->denormalize(
            json_decode($game, true, 512, JSON_THROW_ON_ERROR),
            Game::class
        );
    }
}
