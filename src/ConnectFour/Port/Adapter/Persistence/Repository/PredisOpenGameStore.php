<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Gaming\Common\Normalizer\Normalizer;
use Gaming\ConnectFour\Application\Game\Query\Model\OpenGames\OpenGame;
use Gaming\ConnectFour\Application\Game\Query\Model\OpenGames\OpenGames;
use Gaming\ConnectFour\Application\Game\Query\Model\OpenGames\OpenGameStore;
use Predis\Client;
use Predis\ClientContextInterface;

final class PredisOpenGameStore implements OpenGameStore
{
    private const int GAME_INFO_EXPIRE_TIME = 5;

    public function __construct(
        private readonly Client $predis,
        private readonly string $storageKey,
        private readonly Normalizer $normalizer
    ) {
    }

    public function save(OpenGame $openGame): void
    {
        $this->predis->pipeline(function (ClientContextInterface $pipeline) use ($openGame): void {
            $pipeline->set(
                $this->storageKeyForGameInfo($openGame->gameId),
                json_encode(
                    $this->normalizer->normalize($openGame, OpenGame::class),
                    JSON_THROW_ON_ERROR
                )
            );

            $pipeline->zadd(
                $this->storageKey,
                [$openGame->gameId => microtime(true)]
            );
        });
    }

    public function remove(string $gameId): void
    {
        $this->predis->pipeline(function ($pipeline) use ($gameId): void {
            $pipeline->zrem($this->storageKey, $gameId);

            $pipeline->expire($this->storageKeyForGameInfo($gameId), self::GAME_INFO_EXPIRE_TIME);
        });
    }

    public function all(int $limit): OpenGames
    {
        $gameIds = $this->predis->zrange($this->storageKey, 0, $limit - 1);
        if (count($gameIds) === 0) {
            return new OpenGames([]);
        }

        return new OpenGames(
            array_map(
                fn(string $openGame): OpenGame => $this->normalizer->denormalize(
                    json_decode($openGame, true, flags: JSON_THROW_ON_ERROR),
                    OpenGame::class
                ),
                $this->predis->mget(array_map($this->storageKeyForGameInfo(...), $gameIds))
            )
        );
    }

    private function storageKeyForGameInfo(string $gameId): string
    {
        return $this->storageKey . ':' . $gameId;
    }
}
