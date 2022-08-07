<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Board;

final class Field
{
    private Point $point;

    private Stone $stone;

    private function __construct(Point $point, Stone $stone)
    {
        $this->point = $point;
        $this->stone = $stone;
    }

    public static function empty(Point $point): Field
    {
        return new self($point, Stone::None);
    }

    public function placeStone(Stone $stone): Field
    {
        return new self($this->point(), $stone);
    }

    public function isEmpty(): bool
    {
        return $this->stone->color() === Stone::None->color();
    }

    public function stone(): Stone
    {
        return $this->stone;
    }

    public function point(): Point
    {
        return $this->point;
    }

    public function __toString(): string
    {
        return (string)$this->stone()->color();
    }
}
