<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Application\Model\OpenChallenges;

final class OpenChallenge
{
    public function __construct(
        public readonly string $challengeId,
        public readonly int $size,
        public readonly ?int $preferredToken,
        public readonly string $timer,
        public readonly string $playerId
    ) {
    }
}
