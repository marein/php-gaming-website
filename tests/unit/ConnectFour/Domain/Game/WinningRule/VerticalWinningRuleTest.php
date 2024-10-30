<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game\WinningRule;

use Gaming\ConnectFour\Domain\Game\Board\Board;
use Gaming\ConnectFour\Domain\Game\Board\Point;
use Gaming\ConnectFour\Domain\Game\Board\Size;
use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\Exception\WinningSequenceLengthTooShortException;
use Gaming\ConnectFour\Domain\Game\WinningRule\VerticalWinningRule;
use Gaming\ConnectFour\Domain\Game\WinningRule\WinningSequence;
use PHPUnit\Framework\TestCase;

class VerticalWinningRuleTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldThrowIfWinningSequenceLengthIsTooShort(): void
    {
        $this->expectException(WinningSequenceLengthTooShortException::class);

        new VerticalWinningRule(3);
    }

    /**
     * @test
     */
    public function itShouldCalculateForWin(): void
    {
        $size = new Size(7, 6);
        $board = Board::empty($size);
        $verticalWinningRule = new VerticalWinningRule(4);

        $this->assertNull($verticalWinningRule->findWinningSequence($board));

        $board = $board->dropStone(Stone::Red, 1);
        $board = $board->dropStone(Stone::Red, 1);
        $board = $board->dropStone(Stone::Red, 1);

        $this->assertNull($verticalWinningRule->findWinningSequence($board));

        $board = $board->dropStone(Stone::Red, 1);

        $this->assertEquals(
            new WinningSequence('vertical', [new Point(1, 3), new Point(1, 4), new Point(1, 5), new Point(1, 6)]),
            $verticalWinningRule->findWinningSequence($board)
        );
    }
}
