<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Domain\Challenge;

use Closure;
use Gaming\Common\Domain\Exception\ConcurrencyException;
use Gaming\TicTacToe\Domain\Challenge\Exception\ChallengeException;

interface Challenges
{
    public function nextIdentity(): ChallengeId;

    /**
     * @throws ConcurrencyException
     */
    public function add(Challenge $challenge): void;

    /**
     * @param Closure(Challenge): void $operation
     *
     * @throws ConcurrencyException
     * @throws ChallengeException
     */
    public function update(ChallengeId $challengeId, Closure $operation): void;
}
