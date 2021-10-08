<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Board;

final class Point
{
    /**
     * @var int
     */
    private int $x;

    /**
     * @var int
     */
    private int $y;

    /**
     * Point constructor.
     *
     * @param int $x
     * @param int $y
     */
    public function __construct(int $x, int $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * Returns the x of the [Point].
     *
     * @return int
     */
    public function x(): int
    {
        return $this->x;
    }

    /**
     * Returns the y of the [Point].
     *
     * @return int
     */
    public function y(): int
    {
        return $this->y;
    }
}
