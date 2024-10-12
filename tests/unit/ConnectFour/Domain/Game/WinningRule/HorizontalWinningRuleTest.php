<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game\WinningRule;

use Gaming\ConnectFour\Domain\Game\Board\Board;
use Gaming\ConnectFour\Domain\Game\Board\Field;
use Gaming\ConnectFour\Domain\Game\Board\Point;
use Gaming\ConnectFour\Domain\Game\Board\Size;
use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\Exception\InvalidNumberOfRequiredMatchesException;
use Gaming\ConnectFour\Domain\Game\WinningRule\HorizontalWinningRule;
use PHPUnit\Framework\TestCase;

class HorizontalWinningRuleTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldThrowAnExceptionIfNumberOfRequiredMatchesIsLowerThanFour(): void
    {
        $this->expectException(InvalidNumberOfRequiredMatchesException::class);

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

        $this->assertNull($horizontalWinningRule->calculate($board));

        $board = $board->dropStone(Stone::Red, 1);
        $board = $board->dropStone(Stone::Red, 2);
        $board = $board->dropStone(Stone::Red, 3);

        $this->assertNull($horizontalWinningRule->calculate($board));

        $board = $board->dropStone(Stone::Red, 4);

        $this->assertEquals(
            [
                Field::empty(new Point(1, 6))->placeStone(Stone::Red),
                Field::empty(new Point(2, 6))->placeStone(Stone::Red),
                Field::empty(new Point(3, 6))->placeStone(Stone::Red),
                Field::empty(new Point(4, 6))->placeStone(Stone::Red)
            ],
            $horizontalWinningRule->calculate($board)
        );
    }
}
