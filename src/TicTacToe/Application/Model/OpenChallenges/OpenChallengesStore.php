<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Application\Model\OpenChallenges;

interface OpenChallengesStore
{
    public function save(OpenChallenge $openChallenge): void;

    public function remove(string $challengeId): void;

    /**
     * @return OpenChallenge[]
     */
    public function all(int $limit): array;
}
