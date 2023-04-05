<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Gaming\Common\Normalizer\Normalizer;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\Game;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\GameFinder;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\GameStore;
use Gaming\ConnectFour\Domain\Game\GameId;
use Predis\ClientInterface;

final class PredisGameStore implements GameStore
{
    public function __construct(
        private readonly ClientInterface $predis,
        private readonly string $storageKeyPrefix,
        private readonly Normalizer $normalizer,
        private readonly GameFinder $fallbackGameFinder
    ) {
    }

    public function find(GameId $gameId): Game
    {
        $storedGame = $this->predis->get($this->storageKeyPrefix . $gameId);

        return $storedGame ? $this->deserializeGame($storedGame) : $this->fallbackGameFinder->find($gameId);
    }

    public function save(Game $game): void
    {
        $this->predis->set(
            $this->storageKeyPrefix . $game->id(),
            $this->serializeGame($game)
        );
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
