<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Domain\Game;

use Gaming\Common\Timer\MoveTimer;
use Gaming\Common\Timer\Timer;
use Gaming\TicTacToe\Domain\Game\Exception\GameException;

final class Configuration
{
    /**
     * @throws GameException
     */
    public function __construct(
        public readonly int $size,
        public readonly ?Token $preferredToken,
        public readonly Timer $timer
    ) {
        if ($size < 3 || $size > 9) {
            throw GameException::sizeOutOfRange($size, 3, 9);
        }
    }

    public static function common(): self
    {
        return new self(3, null, MoveTimer::set(15000));
    }
}
