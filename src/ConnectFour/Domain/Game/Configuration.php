<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game;

use Gaming\ConnectFour\Domain\Game\Board\Size;
use Gaming\ConnectFour\Domain\Game\WinningRule\WinningRules;

final class Configuration
{
    private Size $size;

    private WinningRules $winningRules;

    private function __construct(Size $size, WinningRules $winningRules)
    {
        $this->size = $size;
        $this->winningRules = $winningRules;
    }

    public static function common(): Configuration
    {
        return new self(
            new Size(7, 6),
            WinningRules::standard()
        );
    }

    public static function custom(Size $size, WinningRules $winningRules): Configuration
    {
        return new self($size, $winningRules);
    }

    public function size(): Size
    {
        return $this->size;
    }

    public function winningRules(): WinningRules
    {
        return $this->winningRules;
    }
}
