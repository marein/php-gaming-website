<?php

namespace Gambling\ConnectFour\Application\Game\Query\Model\Game;

final class Field
{
    /**
     * @var int
     */
    private $x;

    /**
     * @var int
     */
    private $y;

    /**
     * @var int
     */
    private $color;

    /**
     * Field constructor.
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
     * @return int
     */
    public function x(): int
    {
        return $this->x;
    }

    /**
     * @return int
     */
    public function y(): int
    {
        return $this->y;
    }

    /**
     * @return int
     */
    public function color(): int
    {
        return $this->color;
    }
}
