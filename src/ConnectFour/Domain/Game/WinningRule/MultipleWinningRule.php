<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\WinningRule;

use Gaming\ConnectFour\Domain\Game\Board\Board;

final class MultipleWinningRule implements WinningRule
{
    /**
     * @param WinningRule[] $winningRules
     */
    public function __construct(
        private readonly array $winningRules
    ) {
    }

    public function findWinningSequence(Board $board): array
    {
        foreach ($this->winningRules as $winningRule) {
            if (count($winningSequence = $winningRule->findWinningSequence($board)) !== 0) {
                return $winningSequence;
            }
        }

        return [];
    }
}
