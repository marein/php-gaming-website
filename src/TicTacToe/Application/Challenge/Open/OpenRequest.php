<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Application\Challenge\Open;

use Gaming\Common\Bus\Request;

/**
 * @implements Request<OpenResponse>
 */
final class OpenRequest implements Request
{
    public function __construct(
        public readonly string $challengerId
    ) {
    }
}
