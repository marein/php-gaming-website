<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Gaming\ConnectFour\Application\Game\Query\Model\Game\GameFinder;
use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GamesByPlayer;
use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GamesByPlayerStore;
use Gaming\ConnectFour\Domain\Game\GameId;
use Predis\Client;
use Predis\ClientContextInterface;

final class PredisGamesByPlayerStore implements GamesByPlayerStore
{
    public function __construct(
        private readonly Client $predis,
        private readonly string $storageKeyPrefix,
        private readonly GameFinder $gameFinder
    ) {
    }

    public function addToPlayer(string $playerId, string $gameId): void
    {
        $this->predis->zadd(
            $this->storageKeyForPlayer($playerId),
            [$gameId => microtime(true)]
        );
    }

    public function removeFromPlayer(string $playerId, string $gameId): void
    {
        $this->predis->zrem(
            $this->storageKeyForPlayer($playerId),
            $gameId
        );
    }

    public function all(string $playerId): GamesByPlayer
    {
        $responses = $this->predis->pipeline(
            function (ClientContextInterface $pipeline) use ($playerId): void {
                $pipeline->zcount($this->storageKeyForPlayer($playerId), '-inf', '+inf');
                $pipeline->zrevrange($this->storageKeyForPlayer($playerId), 0, 100);
            }
        );

        return new GamesByPlayer(
            (int)$responses[0],
            $this->gameFinder->findMany(
                array_map(static fn(string $gameId): GameId => GameId::fromString($gameId), $responses[1])
            )
        );
    }

    private function storageKeyForPlayer(string $playerId): string
    {
        return $this->storageKeyPrefix . $playerId;
    }
}
