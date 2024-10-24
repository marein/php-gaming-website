<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game\WinningRule;

use Gaming\ConnectFour\Domain\Game\Board\Board;
use Gaming\ConnectFour\Domain\Game\Board\Field;
use Gaming\ConnectFour\Domain\Game\Board\Point;
use Gaming\ConnectFour\Domain\Game\Board\Size;
use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\Exception\InvalidNumberOfRequiredMatchesException;
use Gaming\ConnectFour\Domain\Game\WinningRule\VerticalWinningRule;
use PHPUnit\Framework\TestCase;

class VerticalWinningRuleTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldThrowAnExceptionIfNumberOfRequiredMatchesIsLowerThanFour(): void
    {
        $this->expectException(InvalidNumberOfRequiredMatchesException::class);

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

        $this->assertNull($verticalWinningRule->calculate($board));

        $board = $board->dropStone(Stone::Red, 1);
        $board = $board->dropStone(Stone::Red, 1);
        $board = $board->dropStone(Stone::Red, 1);

        $this->assertNull($verticalWinningRule->calculate($board));

        $board = $board->dropStone(Stone::Red, 1);

        $this->assertEquals(
            [
                Field::empty(new Point(1, 3))->placeStone(Stone::Red),
                Field::empty(new Point(1, 4))->placeStone(Stone::Red),
                Field::empty(new Point(1, 5))->placeStone(Stone::Red),
                Field::empty(new Point(1, 6))->placeStone(Stone::Red)
            ],
            $verticalWinningRule->calculate($board)
        );
    }
}
