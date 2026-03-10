<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Application\Challenge\GetOpenChallenges;

use Gaming\Common\Bus\Request;

/**
 * @implements Request<GetOpenChallengesResponse>
 */
final class GetOpenChallengesRequest implements Request
{
    public function __construct(
        public readonly int $limit
    ) {
    }
}
