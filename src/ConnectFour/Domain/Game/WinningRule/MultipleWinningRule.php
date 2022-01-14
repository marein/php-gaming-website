<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\WinningRule;

use Gaming\ConnectFour\Domain\Game\Board\Board;

final class MultipleWinningRule implements WinningRule
{
    /**
     * @var WinningRule[]
     */
    private array $winningRules;

    /**
     * @param WinningRule[] $winningRules
     */
    public function __construct(array $winningRules)
    {
        $this->winningRules = $winningRules;
    }

    public function calculate(Board $board): bool
    {
        foreach ($this->winningRules as $winningRule) {
            if ($winningRule->calculate($board)) {
                return true;
            }
        }

        return false;
    }
}
