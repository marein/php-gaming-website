<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Board;

/**
 * This class fakes the missing enumeration feature.
 */
final class Stone
{
    const NONE = 0;
    const RED = 1;
    const YELLOW = 2;

    /**
     * @var int
     */
    private $color;

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
     * Creates a yellow [Stone].
     *
     * @return Stone
     */
    public static function yellow(): Stone
    {
        return new self(self::YELLOW);
    }

    /**
     * Creates a red [Stone].
     *
     * @return Stone
     */
    public static function red(): Stone
    {
        return new self(self::RED);
    }

    /**
     * Creates a null [Stone].
     *
     * @return Stone
     */
    public static function none(): Stone
    {
        return new self(self::NONE);
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
