<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game;

use Gaming\ConnectFour\Domain\Game\Board\Size;
use Gaming\ConnectFour\Domain\Game\WinningRule\CommonWinningRule;
use Gaming\ConnectFour\Domain\Game\WinningRule\WinningRule;

final class Configuration
{
    /**
     * @var Size
     */
    private Size $size;

    /**
     * @var WinningRule
     */
    private WinningRule $winningRule;

    /**
     * Configuration constructor.
     *
     * @param Size        $size
     * @param WinningRule $winningRule
     */
    private function __construct(Size $size, WinningRule $winningRule)
    {
        $this->size = $size;
        $this->winningRule = $winningRule;
    }

    /*************************************************************
     *                         Factory
     *************************************************************/

    /**
     * Create a common [Configuration].
     *
     * @return Configuration
     */
    public static function common(): Configuration
    {
        return new self(
            new Size(7, 6),
            new CommonWinningRule()
        );
    }

    /**
     * Create a custom [Configuration].
     *
     * @param Size        $size
     * @param WinningRule $winningRule
     *
     * @return Configuration
     */
    public static function custom(Size $size, WinningRule $winningRule): Configuration
    {
        return new self($size, $winningRule);
    }

    /*************************************************************
     *                          Getter
     *************************************************************/

    /**
     * Returns the [Size].
     *
     * @return Size
     */
    public function size(): Size
    {
        return $this->size;
    }

    /**
     * Returns the [WinningRule].
     *
     * @return WinningRule
     */
    public function winningRule(): WinningRule
    {
        return $this->winningRule;
    }
}
