<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Board;

final class Point
{
    public function __construct(
        public readonly int $x,
        public readonly int $y
    ) {
    }

    /**
     * @deprecated Use the property instead.
     */
    public function x(): int
    {
        return $this->x;
    }

    /**
     * @deprecated Use the property instead.
     */
    public function y(): int
    {
        return $this->y;
    }
}
