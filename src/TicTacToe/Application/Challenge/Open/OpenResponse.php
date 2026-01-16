<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Application\Challenge\Open;

final class OpenResponse
{
    public function __construct(
        public readonly string $challengeId
    ) {
    }
}
