<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Application\Challenge\All;

use Gaming\TicTacToe\Application\Model\OpenChallenges\OpenChallengesStore;

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
