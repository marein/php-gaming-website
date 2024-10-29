<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game\WinningRule;

use Gaming\ConnectFour\Domain\Game\Board\Board;
use Gaming\ConnectFour\Domain\Game\Board\Point;
use Gaming\ConnectFour\Domain\Game\Board\Size;
use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\Exception\WinningSequenceLengthTooShortException;
use Gaming\ConnectFour\Domain\Game\WinningRule\HorizontalWinningRule;
use PHPUnit\Framework\TestCase;

class HorizontalWinningRuleTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldThrowIfWinningSequenceLengthIsTooShort(): void
    {
        $this->expectException(WinningSequenceLengthTooShortException::class);

        new HorizontalWinningRule(3);
    }

    /**
     * @test
     */
    public function itShouldCalculateForWin(): void
    {
        $size = new Size(7, 6);
        $board = Board::empty($size);
        $horizontalWinningRule = new HorizontalWinningRule(4);

        $this->assertCount(0, $horizontalWinningRule->findWinningSequence($board));

        $board = $board->dropStone(Stone::Red, 1);
        $board = $board->dropStone(Stone::Red, 2);
        $board = $board->dropStone(Stone::Red, 3);

        $this->assertCount(0, $horizontalWinningRule->findWinningSequence($board));

        $board = $board->dropStone(Stone::Red, 4);

        $this->assertEquals(
            [new Point(1, 6), new Point(2, 6), new Point(3, 6), new Point(4, 6)],
            $horizontalWinningRule->findWinningSequence($board)
        );
    }
}
