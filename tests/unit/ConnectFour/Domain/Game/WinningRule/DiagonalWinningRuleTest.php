<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game\WinningRule;

use Gaming\ConnectFour\Domain\Game\Board\Board;
use Gaming\ConnectFour\Domain\Game\Board\Point;
use Gaming\ConnectFour\Domain\Game\Board\Size;
use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\Exception\WinningSequenceLengthTooShortException;
use Gaming\ConnectFour\Domain\Game\WinningRule\DiagonalWinningRule;
use Gaming\ConnectFour\Domain\Game\WinningRule\WinningSequence;
use PHPUnit\Framework\TestCase;

class DiagonalWinningRuleTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldThrowIfWinningSequenceLengthIsTooShort(): void
    {
        $this->expectException(WinningSequenceLengthTooShortException::class);

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

        $this->assertNull($diagonalWinningRule->findWinningSequence($board));

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

        $this->assertNull($diagonalWinningRule->findWinningSequence($board));

        $board = $board->dropStone(Stone::Red, 4);

        $this->assertEquals(
            new WinningSequence('diagonal', [new Point(1, 6), new Point(2, 5), new Point(3, 4), new Point(4, 3)]),
            $diagonalWinningRule->findWinningSequence($board)
        );

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

        $this->assertNull($diagonalWinningRule->findWinningSequence($board));

        $board = $board->dropStone(Stone::Red, 4);

        $this->assertEquals(
            new WinningSequence('diagonal', [new Point(4, 3), new Point(5, 4), new Point(6, 5), new Point(7, 6)]),
            $diagonalWinningRule->findWinningSequence($board)
        );
    }
}
