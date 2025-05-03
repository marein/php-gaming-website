<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Gaming\ConnectFour\Application\Game\Query\PlayerSearchStatistics\PlayerSearchStatisticsResponse;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\GameFinder;
use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GamesByPlayer;
use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GamesByPlayerStore;
use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\State;
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

    public function addRunning(string $gameId, string $playerOne, string $playerTwo): void
    {
        $this->predis->pipeline(
            function (ClientContextInterface $pipeline) use ($gameId, $playerOne, $playerTwo): void {
                $pipeline->zadd($this->keyForPlayer($playerOne, State::ALL), [$gameId => microtime(true)]);
                $pipeline->zadd($this->keyForPlayer($playerOne, State::RUNNING), [$gameId => microtime(true)]);
                $pipeline->zadd($this->keyForPlayer($playerTwo, State::ALL), [$gameId => microtime(true)]);
                $pipeline->zadd($this->keyForPlayer($playerTwo, State::RUNNING), [$gameId => microtime(true)]);
            }
        );
    }

    public function addDraw(string $gameId, string $playerOne, string $playerTwo): void
    {
        $this->predis->pipeline(
            function (ClientContextInterface $pipeline) use ($gameId, $playerOne, $playerTwo): void {
                $pipeline->zrem($this->keyForPlayer($playerOne, State::RUNNING), $gameId);
                $pipeline->zadd($this->keyForPlayer($playerOne, State::DRAWN), [$gameId => microtime(true)]);
                $pipeline->zrem($this->keyForPlayer($playerTwo, State::RUNNING), $gameId);
                $pipeline->zadd($this->keyForPlayer($playerTwo, State::DRAWN), [$gameId => microtime(true)]);
            }
        );
    }

    public function addWin(string $gameId, string $winnerId, string $loserId): void
    {
        $this->predis->pipeline(
            function (ClientContextInterface $pipeline) use ($gameId, $winnerId, $loserId): void {
                $pipeline->zrem($this->keyForPlayer($winnerId, State::RUNNING), $gameId);
                $pipeline->zadd($this->keyForPlayer($winnerId, State::WON), [$gameId => microtime(true)]);
                $pipeline->zrem($this->keyForPlayer($loserId, State::RUNNING), $gameId);
                $pipeline->zadd($this->keyForPlayer($loserId, State::LOST), [$gameId => microtime(true)]);
            }
        );
    }

    public function addAbort(string $gameId, string $playerOne, string $playerTwo): void
    {
        $this->predis->pipeline(
            function (ClientContextInterface $pipeline) use ($gameId, $playerOne, $playerTwo): void {
                $pipeline->zrem($this->keyForPlayer($playerOne, State::ALL), $gameId);
                $pipeline->zrem($this->keyForPlayer($playerOne, State::RUNNING), $gameId);
                $pipeline->zrem($this->keyForPlayer($playerTwo, State::ALL), $gameId);
                $pipeline->zrem($this->keyForPlayer($playerTwo, State::RUNNING), $gameId);
            }
        );
    }

    public function searchStatistics(string $playerId): PlayerSearchStatisticsResponse
    {
        return new PlayerSearchStatisticsResponse(
            array_combine(
                array_map(static fn(State $s): string => $s->value, State::cases()),
                $this->predis->pipeline(
                    function (ClientContextInterface $pipeline) use ($playerId): void {
                        foreach (State::cases() as $state) {
                            $pipeline->zcard($this->keyForPlayer($playerId, $state));
                        }
                    }
                )
            )
        );
    }

    public function search(string $playerId, State $state, int $page, int $limit): GamesByPlayer
    {
        $offset = max(0, $page - 1) * $limit;

        $responses = $this->predis->pipeline(
            function (ClientContextInterface $pipeline) use ($playerId, $state, $offset, $limit): void {
                $pipeline->zcard($this->keyForPlayer($playerId, $state));
                $pipeline->zrevrange($this->keyForPlayer($playerId, $state), $offset, $offset + $limit - 1);
            }
        );

        return new GamesByPlayer(
            (int)$responses[0],
            $this->gameFinder->findMany(
                array_map(static fn(string $gameId): GameId => GameId::fromString($gameId), $responses[1])
            )
        );
    }

    private function keyForPlayer(string $playerId, State $state): string
    {
        return $this->storageKeyPrefix . $playerId . ':' . $state->value;
    }
}
