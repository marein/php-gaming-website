<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Board;

use Gaming\ConnectFour\Domain\Game\Exception\InvalidSizeException;

final class Size
{
    /**
     * @var int
     */
    private int $width;

    /**
     * @var int
     */
    private int $height;

    /**
     * Size constructor.
     *
     * @param int $width
     * @param int $height
     *
     * @throws InvalidSizeException
     */
    public function __construct(int $width, int $height)
    {
        if ($width < 2 || $height < 2) {
            throw new InvalidSizeException('Width and height must be greater then 1.');
        }

        if (($width * $height) % 2 !== 0) {
            throw new InvalidSizeException('Product of width and height must be an even number.');
        }

        $this->height = $height;
        $this->width = $width;
    }

    /**
     * Returns the width of the [Size].
     *
     * @return int
     */
    public function width(): int
    {
        return $this->width;
    }

    /**
     * Returns the height of the [Size].
     *
     * @return int
     */
    public function height(): int
    {
        return $this->height;
    }
}
