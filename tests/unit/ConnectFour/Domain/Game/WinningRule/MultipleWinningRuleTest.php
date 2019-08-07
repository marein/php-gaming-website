<?php
declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game\WinningRule;

use Gaming\ConnectFour\Domain\Game\Board\Board;
use Gaming\ConnectFour\Domain\Game\Board\Size;
use Gaming\ConnectFour\Domain\Game\WinningRule\MultipleWinningRule;
use Gaming\ConnectFour\Domain\Game\WinningRule\WinningRule;
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
