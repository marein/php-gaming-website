<?php

namespace Gambling\ConnectFour\Domain\Game\WinningRule;

use Gambling\ConnectFour\Domain\Game\Board;

final class CommonWinningRule implements WinningRule
{
    /**
     * @var WinningRule
     */
    private $winningRule;

    /**
     * CommonWinningRule constructor.
     */
    public function __construct()
    {
        $this->winningRule = new MultipleWinningRule([
            new VerticalWinningRule(4),
            new HorizontalWinningRule(4),
            new DiagonalWinningRule(4)
        ]);
    }

    /**
     * @inheritdoc
     */
    public function calculate(Board $board): bool
    {
        return $this->winningRule->calculate($board);
    }
}
