<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game;

use Gaming\ConnectFour\Domain\Game\Board\Size;
use Gaming\ConnectFour\Domain\Game\WinningRule\CommonWinningRule;
use Gaming\ConnectFour\Domain\Game\WinningRule\WinningRule;

final class Configuration
{
    private Size $size;

    private WinningRule $winningRule;

    private function __construct(Size $size, WinningRule $winningRule)
    {
        $this->size = $size;
        $this->winningRule = $winningRule;
    }

    public static function common(): Configuration
    {
        return new self(
            new Size(7, 6),
            new CommonWinningRule()
        );
    }

    public static function custom(Size $size, WinningRule $winningRule): Configuration
    {
        return new self($size, $winningRule);
    }

    public function size(): Size
    {
        return $this->size;
    }

    public function winningRule(): WinningRule
    {
        return $this->winningRule;
    }
}
