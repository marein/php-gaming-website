<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\Model\Game;

use JsonSerializable;

final class Move implements JsonSerializable
{
    private int $x;

    private int $y;

    private int $color;

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
