<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\WinningRule;

use Gaming\ConnectFour\Domain\Game\Board\Board;

final class WinningRules
{
    /**
     * @param WinningRule[] $winningRules
     */
    public function __construct(
        private readonly array $winningRules
    ) {
    }

    public static function standard(): self
    {
        return new self(
            [
                new VerticalWinningRule(4),
                new HorizontalWinningRule(4),
                new DiagonalWinningRule(4)
            ]
        );
    }

    /**
     * @return WinningSequence[]
     */
    public function findWinningSequences(Board $board): array
    {
        $winningSequences = array_map(
            static fn(WinningRule $winningRule): ?WinningSequence => $winningRule->findWinningSequence($board),
            $this->winningRules
        );

        return array_values(array_filter($winningSequences));
    }
}
