<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

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
                $pipeline->zadd($this->keyForPlayer($playerOne, State::ALL->value), [$gameId => microtime(true)]);
                $pipeline->zadd($this->keyForPlayer($playerOne, State::RUNNING->value), [$gameId => microtime(true)]);
                $pipeline->zadd($this->keyForPlayer($playerTwo, State::ALL->value), [$gameId => microtime(true)]);
                $pipeline->zadd($this->keyForPlayer($playerTwo, State::RUNNING->value), [$gameId => microtime(true)]);
            }
        );
    }

    public function addDraw(string $gameId, string $playerOne, string $playerTwo): void
    {
        $this->predis->pipeline(
            function (ClientContextInterface $pipeline) use ($gameId, $playerOne, $playerTwo): void {
                $pipeline->zrem($this->keyForPlayer($playerOne, State::RUNNING->value), $gameId);
                $pipeline->zadd($this->keyForPlayer($playerOne, State::DRAWN->value), [$gameId => microtime(true)]);
                $pipeline->zrem($this->keyForPlayer($playerTwo, State::RUNNING->value), $gameId);
                $pipeline->zadd($this->keyForPlayer($playerTwo, State::DRAWN->value), [$gameId => microtime(true)]);
            }
        );
    }

    public function addWin(string $gameId, string $winnerId, string $loserId): void
    {
        $this->predis->pipeline(
            function (ClientContextInterface $pipeline) use ($gameId, $winnerId, $loserId): void {
                $pipeline->zrem($this->keyForPlayer($winnerId, State::RUNNING->value), $gameId);
                $pipeline->zadd($this->keyForPlayer($winnerId, State::WON->value), [$gameId => microtime(true)]);
                $pipeline->zrem($this->keyForPlayer($loserId, State::RUNNING->value), $gameId);
                $pipeline->zadd($this->keyForPlayer($loserId, State::LOST->value), [$gameId => microtime(true)]);
            }
        );
    }

    public function addAbort(string $gameId, string $playerOne, string $playerTwo): void
    {
        $this->predis->pipeline(
            function (ClientContextInterface $pipeline) use ($gameId, $playerOne, $playerTwo): void {
                $pipeline->zrem($this->keyForPlayer($playerOne, State::ALL->value), $gameId);
                $pipeline->zrem($this->keyForPlayer($playerOne, State::RUNNING->value), $gameId);
                $pipeline->zrem($this->keyForPlayer($playerTwo, State::ALL->value), $gameId);
                $pipeline->zrem($this->keyForPlayer($playerTwo, State::RUNNING->value), $gameId);
            }
        );
    }

    public function search(string $playerId, State $state, int $page, int $limit): GamesByPlayer
    {
        $offset = max(0, $page - 1) * $limit;
        $states = array_map(static fn(State $s): string => $s->value, State::cases());

        $responses = $this->predis->pipeline(
            function (ClientContextInterface $pipeline) use ($playerId, $states, $state, $offset, $limit): void {
                $pipeline->zrevrange(
                    $this->keyForPlayer($playerId, $state->value),
                    $offset,
                    $offset + $limit - 1
                );

                foreach ($states as $state) {
                    $pipeline->zcard($this->keyForPlayer($playerId, $state));
                }
            }
        );

        return new GamesByPlayer(
            $totals = array_combine($states, array_slice($responses, 1)),
            $totals[$state->value],
            $this->gameFinder->findMany(
                array_map(static fn(string $gameId): GameId => GameId::fromString($gameId), $responses[0])
            )
        );
    }

    private function keyForPlayer(string $playerId, string $state): string
    {
        return $this->storageKeyPrefix . $playerId . ':' . $state;
    }
}
