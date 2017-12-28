<?php

namespace Gambling\ConnectFour\Application\Game\Query\Model\Game;

final class Move
{
    /**
     * The x coordinate.
     *
     * @var int
     */
    private $x;

    /**
     * The y coordinate.
     *
     * @var int
     */
    private $y;

    /**
     * The color. Can be 0, 1 or 2. 0 means empty.
     *
     * @var int
     */
    private $color;

    /**
     * Move constructor.
     *
     * @param int $x
     * @param int $y
     * @param int $color
     */
    public function __construct(int $x, int $y, int $color)
    {
        $this->x = $x;
        $this->y = $y;
        $this->color = $color;
    }

    /**
     * Returns the y coordinate.
     *
     * @return int
     */
    public function x(): int
    {
        return $this->x;
    }

    /**
     * Returns the y coordinate.
     *
     * @return int
     */
    public function y(): int
    {
        return $this->y;
    }

    /**
     * Returns the color. Can be 0, 1 or 2. 0 means empty.
     *
     * @return int
     */
    public function color(): int
    {
        return $this->color;
    }
}
