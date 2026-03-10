<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Application\Challenge\GetById;

use Gaming\Common\Bus\Request;

/**
 * @implements Request<GetByIdResponse>
 */
final class GetByIdRequest implements Request
{
    public function __construct(
        public readonly string $challengeId
    ) {
    }
}
