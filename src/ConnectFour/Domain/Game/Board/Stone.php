<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Board;

/**
 * This class fakes the missing enumeration feature.
 */
final class Stone
{
    private int $color;

    private function __construct(int $color)
    {
        $this->color = $color;
    }

    public static function none(): Stone
    {
        static $stone;
        return $stone ??= new Stone(0);
    }

    public static function red(): Stone
    {
        static $stone;
        return $stone ??= new Stone(1);
    }

    public static function yellow(): Stone
    {
        static $stone;
        return $stone ??= new Stone(2);
    }

    public function color(): int
    {
        return $this->color;
    }
}
