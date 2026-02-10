<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Application\Challenge\GetOpenChallenges;

use Gaming\TicTacToe\Application\Model\OpenChallenges\OpenChallenge;

final class GetOpenChallengesResponse
{
    /**
     * @param OpenChallenge[] $openChallenges
     */
    public function __construct(
        public readonly array $openChallenges
    ) {
    }
}
