<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\WinningRule;

use Gaming\ConnectFour\Domain\Game\Board\Board;
use Gaming\ConnectFour\Domain\Game\Board\Field;

interface WinningRule
{
    /**
     * @return Field[]|null
     */
    public function calculate(Board $board): ?array;
}
