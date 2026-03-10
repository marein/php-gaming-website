<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Application\Challenge\GetOpenChallenges;

use Gaming\TicTacToe\Application\Model\OpenChallenges\OpenChallengesStore;

final class GetOpenChallengesHandler
{
    public function __construct(
        private readonly OpenChallengesStore $openChallengesStore
    ) {
    }

    public function __invoke(GetOpenChallengesRequest $request): GetOpenChallengesResponse
    {
        return new GetOpenChallengesResponse($this->openChallengesStore->all($request->limit));
    }
}
