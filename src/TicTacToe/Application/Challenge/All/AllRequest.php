<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Application\Challenge\All;

use Gaming\Common\Bus\Request;

/**
 * @implements Request<AllResponse>
 */
final class AllRequest implements Request
{
    public function __construct(
        public readonly int $limit
    ) {
    }
}
