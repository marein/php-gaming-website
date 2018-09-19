<?php
declare(strict_types=1);

namespace Gambling\ConnectFour\Application\Game\Query\Model\Game;

final class Move implements \JsonSerializable
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
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'x'     => $this->x,
            'y'     => $this->y,
            'color' => $this->color
        ];
    }
}
