<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Board;

/**
 * This class fakes the missing enumeration feature.
 */
final class Stone
{
    /**
     * @var int
     */
    private int $color;

    /**
     * Stone constructor.
     *
     * @param int $color
     */
    private function __construct(int $color)
    {
        $this->color = $color;
    }

    /**
     * Creates a null [Stone].
     *
     * @return Stone
     */
    public static function none(): Stone
    {
        static $stone;
        return $stone ?: $stone = new Stone(0);
    }

    /**
     * Creates a red [Stone].
     *
     * @return Stone
     */
    public static function red(): Stone
    {
        static $stone;
        return $stone ?: $stone = new Stone(1);
    }

    /**
     * Creates a yellow [Stone].
     *
     * @return Stone
     */
    public static function yellow(): Stone
    {
        static $stone;
        return $stone ?: $stone = new Stone(2);
    }

    /**
     * Returns the color of the [Stone].
     *
     * @return int
     */
    public function color(): int
    {
        return $this->color;
    }
}
