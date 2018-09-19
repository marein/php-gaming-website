<?php
declare(strict_types=1);

namespace Gambling\ConnectFour\Domain\Game\WinningRule;

use Gambling\ConnectFour\Domain\Game\Board\Board;

final class MultipleWinningRule implements WinningRule
{
    /**
     * @var WinningRule[]
     */
    private $winningRules;

    /**
     * MultipleWinningRule constructor.
     *
     * @param WinningRule[] $winningRules
     */
    public function __construct(array $winningRules)
    {
        $this->winningRules = $winningRules;
    }

    /**
     * @inheritdoc
     */
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
