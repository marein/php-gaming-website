<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\WinningRule;

use Gaming\ConnectFour\Domain\Game\Board\Point;

final class WinningSequence
{
    /**
     * @param Point[] $points
     */
    public function __construct(
        public readonly string $rule,
        public readonly array $points
    ) {
    }
}
