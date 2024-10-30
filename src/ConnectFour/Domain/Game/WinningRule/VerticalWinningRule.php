<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\WinningRule;

use Gaming\ConnectFour\Domain\Game\Board\Board;
use Gaming\ConnectFour\Domain\Game\Board\Point;

final class VerticalWinningRule extends SequenceBasedWinningRule
{
    protected function findFields(Board $board, Point $point): array
    {
        return $board->findFieldsByColumn($point->x());
    }

    protected function name(): string
    {
        return 'vertical';
    }
}
