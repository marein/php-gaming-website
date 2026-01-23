<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Port\Adapter\Persistence\Repository;

use Gaming\Common\Normalizer\Normalizer;
use Gaming\TicTacToe\Application\Model\OpenChallenges\OpenChallenge;
use Gaming\TicTacToe\Application\Model\OpenChallenges\OpenChallengesStore;
use Predis\Client;
use Predis\ClientContextInterface;

final class PredisOpenChallengesStore implements OpenChallengesStore
{
    private const int CHALLENGE_INFO_EXPIRE_TIME = 5;

    public function __construct(
        private readonly Client $predis,
        private readonly string $storageKey,
        private readonly Normalizer $normalizer
    ) {
    }

    public function save(OpenChallenge $openChallenge): void
    {
        $this->predis->pipeline(function (ClientContextInterface $pipeline) use ($openChallenge): void {
            $pipeline->set(
                $this->storageKeyForChallengeInfo($openChallenge->challengeId),
                json_encode(
                    $this->normalizer->normalize($openChallenge, OpenChallenge::class),
                    JSON_THROW_ON_ERROR
                )
            );

            $pipeline->zadd(
                $this->storageKey,
                [$openChallenge->challengeId => microtime(true)]
            );
        });
    }

    public function remove(string $challengeId): void
    {
        $this->predis->pipeline(function ($pipeline) use ($challengeId): void {
            $pipeline->zrem($this->storageKey, $challengeId);

            $pipeline->expire($this->storageKeyForChallengeInfo($challengeId), self::CHALLENGE_INFO_EXPIRE_TIME);
        });
    }

    public function all(int $limit): array
    {
        $challengeIds = $this->predis->zrange($this->storageKey, 0, $limit - 1);
        if (count($challengeIds) === 0) {
            return [];
        }

        return array_map(
            fn(string $openChallenge): OpenChallenge => $this->normalizer->denormalize(
                json_decode($openChallenge, true, flags: JSON_THROW_ON_ERROR),
                OpenChallenge::class
            ),
            $this->predis->mget(array_map($this->storageKeyForChallengeInfo(...), $challengeIds))
        );
    }

    private function storageKeyForChallengeInfo(string $challengeId): string
    {
        return $this->storageKey . ':' . $challengeId;
    }
}
