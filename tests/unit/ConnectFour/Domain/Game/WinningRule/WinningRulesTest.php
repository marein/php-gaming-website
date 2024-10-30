<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game\WinningRule;

use Gaming\ConnectFour\Domain\Game\Board\Board;
use Gaming\ConnectFour\Domain\Game\Board\Point;
use Gaming\ConnectFour\Domain\Game\Board\Size;
use Gaming\ConnectFour\Domain\Game\WinningRule\WinningRule;
use Gaming\ConnectFour\Domain\Game\WinningRule\WinningRules;
use Gaming\ConnectFour\Domain\Game\WinningRule\WinningSequence;
use PHPUnit\Framework\TestCase;

class WinningRulesTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldCalculateForWin(): void
    {
        $size = new Size(7, 6);
        $board = Board::empty($size);

        $first = $this->createMock(WinningRule::class);
        $first->method('findWinningSequence')->willReturn(null);

        $second = $this->createMock(WinningRule::class);
        $second->method('findWinningSequence')->willReturn(null);

        $winningRules = new WinningRules([$first, $second]);

        $this->assertCount(0, $winningRules->findWinningSequences($board));

        $first = $this->createMock(WinningRule::class);
        $first->method('findWinningSequence')->willReturn(new WinningSequence('first', [new Point(1, 6)]));

        $second = $this->createMock(WinningRule::class);
        $second->method('findWinningSequence')->willReturn(null);

        $third = $this->createMock(WinningRule::class);
        $third->method('findWinningSequence')->willReturn(new WinningSequence('second', [new Point(4, 3)]));

        $winningRules = new WinningRules([$first, $second, $third]);

        $this->assertEquals(
            [new WinningSequence('first', [new Point(1, 6)]), new WinningSequence('second', [new Point(4, 3)])],
            $winningRules->findWinningSequences($board)
        );
    }
}
