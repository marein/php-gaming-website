<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\WinningRule;

use Gaming\ConnectFour\Domain\Game\Board\Board;

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
