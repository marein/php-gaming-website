<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Board;

use Gaming\ConnectFour\Domain\Game\Exception\InvalidSizeException;

final class Size
{
    /**
     * @throws InvalidSizeException
     */
    public function __construct(
        public readonly int $width,
        public readonly int $height
    ) {
        if ($width < 2 || $height < 2) {
            throw new InvalidSizeException('Width and height must be greater then 1.');
        }
    }

    /**
     * @deprecated Use property instead.
     */
    public function width(): int
    {
        return $this->width;
    }

    /**
     * @deprecated Use property instead.
     */
    public function height(): int
    {
        return $this->height;
    }
}
