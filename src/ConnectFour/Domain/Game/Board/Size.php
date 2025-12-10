<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Board;

use Gaming\ConnectFour\Domain\Game\Exception\InvalidSizeException;

final class Size
{
    private int $width;

    private int $height;

    /**
     * @throws InvalidSizeException
     */
    public function __construct(int $width, int $height)
    {
        if ($width < 2 || $height < 2) {
            throw InvalidSizeException::tooSmall($width, $height);
        }

        if (($width * $height) % 2 !== 0) {
            throw InvalidSizeException::productNotEven($width, $height);
        }

        $this->height = $height;
        $this->width = $width;
    }

    public function width(): int
    {
        return $this->width;
    }

    public function height(): int
    {
        return $this->height;
    }
}
