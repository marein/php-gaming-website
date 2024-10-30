<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\WinningRule;

use Gaming\ConnectFour\Domain\Game\Board\Board;

interface WinningRule
{
    public function findWinningSequence(Board $board): ?WinningSequence;
}
