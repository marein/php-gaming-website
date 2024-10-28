<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\WinningRule;

use Gaming\ConnectFour\Domain\Game\Board\Board;
use Gaming\ConnectFour\Domain\Game\Board\Point;

interface WinningRule
{
    /**
     * @return Point[]
     */
    public function findWinningSequence(Board $board): array;
}
