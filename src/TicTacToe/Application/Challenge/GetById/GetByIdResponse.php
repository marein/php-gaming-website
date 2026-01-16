<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Application\Challenge\GetById;

use Gaming\TicTacToe\Application\Model\Challenge;

final class GetByIdResponse
{
    public function __construct(
        public readonly Challenge $challenge
    ) {
    }
}
