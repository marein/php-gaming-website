<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\Model\Game;

use JsonSerializable;

final class Move implements JsonSerializable
{
    /**
     * The x coordinate.
     *
     * @var int
     */
    private int $x;

    /**
     * The y coordinate.
     *
     * @var int
     */
    private int $y;

    /**
     * The color. Can be 0, 1 or 2. 0 means empty.
     *
     * @var int
     */
    private int $color;

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

    public function jsonSerialize(): mixed
    {
        return [
            'x' => $this->x,
            'y' => $this->y,
            'color' => $this->color
        ];
    }
}
