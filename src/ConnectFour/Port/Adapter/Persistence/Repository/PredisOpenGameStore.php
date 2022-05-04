<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Gaming\ConnectFour\Application\Game\Query\Model\OpenGames\OpenGame;
use Gaming\ConnectFour\Application\Game\Query\Model\OpenGames\OpenGames;
use Gaming\ConnectFour\Application\Game\Query\Model\OpenGames\OpenGameStore;
use Predis\ClientInterface;

final class PredisOpenGameStore implements OpenGameStore
{
    public function __construct(
        private readonly ClientInterface $predis,
        private readonly string $storageKey
    ) {
    }

    public function save(OpenGame $openGame): void
    {
        $this->predis->hset(
            $this->storageKey,
            $openGame->gameId(),
            json_encode(
                [
                    'gameId' => $openGame->gameId(),
                    'playerId' => $openGame->playerId()
                ],
                JSON_THROW_ON_ERROR
            )
        );
    }

    public function remove(string $gameId): void
    {
        $this->predis->hdel($this->storageKey, [$gameId]);
    }

    public function all(): OpenGames
    {
        return new OpenGames(
            array_map(
                static function (string $value): OpenGame {
                    $payload = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                    return new OpenGame($payload['gameId'], $payload['playerId']);
                },
                array_values($this->predis->hgetall($this->storageKey))
            )
        );
    }
}
