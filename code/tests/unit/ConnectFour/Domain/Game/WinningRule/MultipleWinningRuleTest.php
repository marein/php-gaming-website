<?php

namespace Gambling\ConnectFour\Domain\Game\WinningRule;

use Gambling\ConnectFour\Domain\Game\Board\Board;
use Gambling\ConnectFour\Domain\Game\Board\Size;
use PHPUnit\Framework\TestCase;

class MultipleWinningRuleTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldCalculateForWin(): void
    {
        $size = new Size(7, 6);
        $board = Board::empty($size);

        $first = $this->createMock(WinningRule::class);
        $first->method('calculate')->willReturn(false);

        $second = $this->createMock(WinningRule::class);
        $second->method('calculate')->willReturn(false);

        $rule = new MultipleWinningRule([$first, $second]);

        $this->assertFalse($rule->calculate($board));

        $first = $this->createMock(WinningRule::class);
        $first->method('calculate')->willReturn(true);

        $second = $this->createMock(WinningRule::class);
        $second->method('calculate')->willReturn(false);

        $rule = new MultipleWinningRule([$first, $second]);

        $this->assertTrue($rule->calculate($board));
    }
}
