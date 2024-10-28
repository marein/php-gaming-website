<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\WinningRule;

use Gaming\ConnectFour\Domain\Game\Board\Board;
use Gaming\ConnectFour\Domain\Game\Board\Field;
use Gaming\ConnectFour\Domain\Game\Board\Point;

final class DiagonalWinningRule extends SequenceBasedWinningRule
{
    protected function findFields(Board $board, Point $point): array
    {
        return [
            ...$board->findFieldsInMainDiagonalByPoint($point),
            Field::empty(new Point(0, 0)),
            ...$board->findFieldsInCounterDiagonalByPoint($point)
        ];
    }
}
