<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\Model\Game;

final class Point
{
    public function __construct(
        public readonly int $x,
        public readonly int $y
    ) {
    }
}