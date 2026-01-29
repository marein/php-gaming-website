<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Application\Challenge\Withdraw;

use Gaming\TicTacToe\Domain\Challenge\Challenge;
use Gaming\TicTacToe\Domain\Challenge\ChallengeId;
use Gaming\TicTacToe\Domain\Challenge\Challenges;

final class WithdrawHandler
{
    public function __construct(
        public readonly Challenges $challenges
    ) {
    }

    public function __invoke(WithdrawRequest $request): void
    {
        $this->challenges->update(
            ChallengeId::fromString($request->challengeId),
            static fn(Challenge $challenge) => $challenge->withdraw($request->playerId)
        );
    }
}
