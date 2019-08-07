<?php
declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game\WinningRule;

use Gaming\ConnectFour\Domain\Game\Board\Board;
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

        $this->assertFalse($horizontalWinningRule->calculate($board));

        $board = $board->dropStone(Stone::red(), 1);
        $board = $board->dropStone(Stone::red(), 2);
        $board = $board->dropStone(Stone::red(), 3);

        $this->assertFalse($horizontalWinningRule->calculate($board));

        $board = $board->dropStone(Stone::red(), 4);

        $this->assertTrue($horizontalWinningRule->calculate($board));
    }
}
