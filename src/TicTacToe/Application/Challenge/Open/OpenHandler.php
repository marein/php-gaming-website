<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Application\Challenge\Open;

use Gaming\TicTacToe\Domain\Challenge\Challenge;
use Gaming\TicTacToe\Domain\Challenge\Challenges;

final class OpenHandler
{
    public function __construct(
        public readonly Challenges $challenges
    ) {
    }

    public function __invoke(OpenRequest $request): OpenResponse
    {
        $challengeId = $this->challenges->nextIdentity();

        $this->challenges->add(Challenge::open($challengeId, $request->challengerId));

        return new OpenResponse($challengeId->toString());
    }
}
