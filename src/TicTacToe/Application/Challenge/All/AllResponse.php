<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Application\Challenge\All;

use Gaming\TicTacToe\Application\Model\OpenChallenges\OpenChallenge;

final class AllResponse
{
    /**
     * @param OpenChallenge[] $openChallenges
     */
    public function __construct(
        public readonly array $openChallenges
    ) {
    }
}
