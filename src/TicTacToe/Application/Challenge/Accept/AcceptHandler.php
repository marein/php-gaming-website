<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Application\Challenge\Accept;

use Gaming\TicTacToe\Domain\Challenge\Challenge;
use Gaming\TicTacToe\Domain\Challenge\ChallengeId;
use Gaming\TicTacToe\Domain\Challenge\Challenges;

final class AcceptHandler
{
    public function __construct(
        public readonly Challenges $challenges
    ) {
    }

    public function __invoke(AcceptRequest $request): void
    {
        $this->challenges->update(
            ChallengeId::fromString($request->challengeId),
            static fn(Challenge $challenge) => $challenge->accept($request->playerId)
        );
    }
}
