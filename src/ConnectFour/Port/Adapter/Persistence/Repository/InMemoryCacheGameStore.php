<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Gaming\ConnectFour\Application\Game\Query\Model\Game\Game;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\GameStore;
use Gaming\ConnectFour\Domain\Game\GameId;

final class InMemoryCacheGameStore implements GameStore
{
    /**
     * @var Game[]
     */
    private array $cachedGames = [];

    public function __construct(
        private readonly GameStore $gameStore,
        private readonly int $cacheSize
    ) {
    }

    public function find(GameId $gameId): Game
    {
        return $this->cachedGames[$gameId->toString()] ?? $this->gameStore->find($gameId);
    }

    public function findMany(array $gameIds): array
    {
        return $this->gameStore->findMany($gameIds);
    }

    public function persist(Game $game): void
    {
        if ($game->finished()) {
            unset($this->cachedGames[$game->id()]);
        }

        $this->removeFirstElementIfLimitHasBeenExceeded();

        $this->gameStore->persist($game);

        $this->cachedGames[$game->id()] = $game;
    }

    public function flush(): void
    {
        $this->gameStore->flush();
    }

    private function removeFirstElementIfLimitHasBeenExceeded(): void
    {
        if (count($this->cachedGames) > $this->cacheSize) {
            array_shift($this->cachedGames);
        }
    }
}
