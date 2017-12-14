<?php

namespace Gambling\ConnectFour\Domain\Game\WinningRule;

use Gambling\ConnectFour\Domain\Game\Board\Board;
use Gambling\ConnectFour\Domain\Game\Board\Size;
use Gambling\ConnectFour\Domain\Game\Board\Stone;
use PHPUnit\Framework\TestCase;

class CommonWinningRuleTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldHaveTheRightRules(): void
    {
        // Test for the right rules directly at property level.
        $commonReflectionProperty = new \ReflectionProperty(CommonWinningRule::class, 'winningRule');
        $commonReflectionProperty->setAccessible(true);
        $multipleReflectionProperty = new \ReflectionProperty(MultipleWinningRule::class, 'winningRules');
        $multipleReflectionProperty->setAccessible(true);

        $common = new CommonWinningRule();

        $multiple = $commonReflectionProperty->getValue($common);

        $this->assertInstanceOf(MultipleWinningRule::class, $multiple);

        $rules = $multipleReflectionProperty->getValue($multiple);

        $this->assertCount(3, $rules);
        $this->assertInstanceOf(VerticalWinningRule::class, $rules[0]);
        $this->assertInstanceOf(HorizontalWinningRule::class, $rules[1]);
        $this->assertInstanceOf(DiagonalWinningRule::class, $rules[2]);
    }

    /**
     * @test
     */
    public function itShouldCalculateForWin(): void
    {
        // Test only one of the above rules because the rules have their own unit tests.
        $size = new Size(7, 6);
        $board = Board::empty($size);
        $commonWinningRule = new CommonWinningRule();

        $board = $board->dropStone(Stone::red(), 1);
        $board = $board->dropStone(Stone::red(), 1);
        $board = $board->dropStone(Stone::red(), 1);
        $board = $board->dropStone(Stone::red(), 1);

        $this->assertTrue($commonWinningRule->calculate($board));
    }
}
