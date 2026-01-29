<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Domain\Game;

enum Token: int
{
    case X = 1;
    case O = 2;

    public static function random(): self
    {
        return self::from(rand(1, 2));
    }
}
