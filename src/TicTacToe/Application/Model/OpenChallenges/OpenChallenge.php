<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Application\Model\OpenChallenges;

final class OpenChallenge
{
    public function __construct(
        public readonly string $challengeId,
        public readonly int $width,
        public readonly int $height,
        public readonly string $playerId
    ) {
    }
}
