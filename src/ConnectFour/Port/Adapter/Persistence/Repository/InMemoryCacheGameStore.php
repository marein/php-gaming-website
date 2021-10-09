<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Gaming\ConnectFour\Application\Game\Query\Model\Game\Game;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\GameStore;

final class InMemoryCacheGameStore implements GameStore
{
    private GameStore $gameStore;

    private int $cacheSize;

    /**
     * @var Game[]
     */
    private array $cachedGames;

    public function __construct(GameStore $gameStore, int $cacheSize)
    {
        $this->gameStore = $gameStore;
        $this->cacheSize = $cacheSize;
        $this->cachedGames = [];
    }

    public function find(string $gameId): Game
    {
        if (array_key_exists($gameId, $this->cachedGames)) {
            return $this->cachedGames[$gameId];
        }

        return $this->gameStore->find($gameId);
    }

    public function save(Game $game): void
    {
        if ($game->finished()) {
            unset($this->cachedGames[$game->id()]);
        }

        $this->removeFirstElementIfLimitHasBeenExceeded();

        $this->gameStore->save($game);

        $this->cachedGames[$game->id()] = $game;
    }

    private function removeFirstElementIfLimitHasBeenExceeded(): void
    {
        if (count($this->cachedGames) > $this->cacheSize) {
            array_shift($this->cachedGames);
        }
    }
}
