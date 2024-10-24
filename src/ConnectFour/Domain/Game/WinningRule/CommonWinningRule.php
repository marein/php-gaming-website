<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\WinningRule;

use Gaming\ConnectFour\Domain\Game\Board\Board;

final class CommonWinningRule implements WinningRule
{
    private WinningRule $winningRule;

    public function __construct()
    {
        $this->winningRule = new MultipleWinningRule(
            [
                new VerticalWinningRule(4),
                new HorizontalWinningRule(4),
                new DiagonalWinningRule(4)
            ]
        );
    }

    public function calculate(Board $board): ?array
    {
        return $this->winningRule->calculate($board);
    }
}
