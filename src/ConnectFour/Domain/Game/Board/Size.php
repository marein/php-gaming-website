<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Board;

use Gaming\ConnectFour\Domain\Game\Exception\GameException;

final class Size
{
    private int $width;

    private int $height;

    /**
     * @throws GameException
     */
    public function __construct(int $width, int $height)
    {
        if ($width < 2 || $height < 2) {
            throw GameException::invalidSizeTooSmall($width, $height);
        }

        if (($width * $height) % 2 !== 0) {
            throw GameException::invalidSizeNotEven($width, $height);
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
