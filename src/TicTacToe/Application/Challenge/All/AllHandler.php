<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Application\Challenge\All;

use Gaming\Common\EventStore\EventStore;
use Gaming\TicTacToe\Application\Model\Challenge;
use Gaming\TicTacToe\Application\Model\OpenChallenges\OpenChallengesStore;
use Gaming\TicTacToe\Domain\Challenge\Event\ChallengeOpened;
use Gaming\TicTacToe\Domain\Challenge\Exception\ChallengeNotFoundException;

final class AllHandler
{
    public function __construct(
        public readonly OpenChallengesStore $openChallengesStore
    ) {
    }

    public function __invoke(AllRequest $request): AllResponse
    {
        return new AllResponse($this->openChallengesStore->all($request->limit));
    }
}
