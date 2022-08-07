<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game\WinningRule;

use Gaming\ConnectFour\Domain\Game\Board\Board;
use Gaming\ConnectFour\Domain\Game\Board\Size;
use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\Exception\InvalidNumberOfRequiredMatchesException;
use Gaming\ConnectFour\Domain\Game\WinningRule\DiagonalWinningRule;
use PHPUnit\Framework\TestCase;

class DiagonalWinningRuleTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldThrowAnExceptionIfNumberOfRequiredMatchesIsLowerThanFour(): void
    {
        $this->expectException(InvalidNumberOfRequiredMatchesException::class);

        new DiagonalWinningRule(3);
    }

    /**
     * @test
     */
    public function itShouldCalculateForWin(): void
    {
        $size = new Size(7, 6);
        $board = Board::empty($size);
        $diagonalWinningRule = new DiagonalWinningRule(4);

        $this->assertFalse($diagonalWinningRule->calculate($board));

        /**
         *    /
         *   /
         *  /
         * /
         */
        $board = $board->dropStone(Stone::Red, 1);
        $board = $board->dropStone(Stone::Red, 2);
        $board = $board->dropStone(Stone::Red, 2);
        $board = $board->dropStone(Stone::Red, 3);
        $board = $board->dropStone(Stone::Red, 3);
        $board = $board->dropStone(Stone::Red, 3);
        $board = $board->dropStone(Stone::Red, 4);
        $board = $board->dropStone(Stone::Red, 4);
        $board = $board->dropStone(Stone::Red, 4);

        $this->assertFalse($diagonalWinningRule->calculate($board));

        $board = $board->dropStone(Stone::Red, 4);

        $this->assertTrue($diagonalWinningRule->calculate($board));

        $board = Board::empty($size);

        /**
         * \
         *  \
         *   \
         *    \
         */
        $board = $board->dropStone(Stone::Red, 7);
        $board = $board->dropStone(Stone::Red, 6);
        $board = $board->dropStone(Stone::Red, 6);
        $board = $board->dropStone(Stone::Red, 5);
        $board = $board->dropStone(Stone::Red, 5);
        $board = $board->dropStone(Stone::Red, 5);
        $board = $board->dropStone(Stone::Red, 4);
        $board = $board->dropStone(Stone::Red, 4);
        $board = $board->dropStone(Stone::Red, 4);

        $this->assertFalse($diagonalWinningRule->calculate($board));

        $board = $board->dropStone(Stone::Red, 4);

        $this->assertTrue($diagonalWinningRule->calculate($board));
    }
}
