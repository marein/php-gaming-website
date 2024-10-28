<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game\WinningRule;

use Gaming\ConnectFour\Domain\Game\Board\Board;
use Gaming\ConnectFour\Domain\Game\Board\Point;
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
        $first->method('findWinningSequence')->willReturn([]);

        $second = $this->createMock(WinningRule::class);
        $second->method('findWinningSequence')->willReturn([]);

        $rule = new MultipleWinningRule([$first, $second]);

        $this->assertCount(0, $rule->findWinningSequence($board));

        $first = $this->createMock(WinningRule::class);
        $first->method('findWinningSequence')->willReturn([new Point(1, 6)]);

        $second = $this->createMock(WinningRule::class);
        $second->method('findWinningSequence')->willReturn([]);

        $rule = new MultipleWinningRule([$first, $second]);

        $this->assertEquals([new Point(1, 6)], $rule->findWinningSequence($board));
    }
}
