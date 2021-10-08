<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\WinningRule;

use Gaming\ConnectFour\Domain\Game\Board\Board;

interface WinningRule
{
    /**
     * Returns true if the rule applies.
     *
     * @param Board $board
     *
     * @return bool
     */
    public function calculate(Board $board): bool;
}
