<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Memory\Domain\Model\Game;

use Gaming\Memory\Domain\Model\Game\Exception\PlayerAlreadyJoinedException;
use Gaming\Memory\Domain\Model\Game\Exception\PlayerNotJoinedException;
use Gaming\Memory\Domain\Model\Game\Exception\PlayerPoolIsEmptyException;
use Gaming\Memory\Domain\Model\Game\Player;
use Gaming\Memory\Domain\Model\Game\PlayerPool;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PlayerPoolTest extends TestCase
{
    #[Test]
    public function itShouldSwitchPlayers(): void
    {
        $first = new Player('0');
        $second = new Player('1');
        $third = new Player('2');
        $fourth = new Player('3');

        $playerPool = PlayerPool::beginWith($first)
            ->join($second)
            ->join($third)
            ->join($fourth);

        $this->assertFalse($playerPool->isEmpty());

        $this->assertEquals($playerPool->current(), $first);

        $playerPool = $playerPool->switch();
        $this->assertEquals($playerPool->current(), $second);

        $playerPool = $playerPool->switch();
        $this->assertEquals($playerPool->current(), $third);

        $playerPool = $playerPool->switch();
        $this->assertEquals($playerPool->current(), $fourth);

        $playerPool = $playerPool->switch();
        $this->assertEquals($playerPool->current(), $first);
    }

    #[Test]
    public function playersCanLeave(): void
    {
        $first = new Player('0');
        $second = new Player('1');
        $third = new Player('2');
        $fourth = new Player('3');

        $playerPool = PlayerPool::beginWith($first)
            ->join($second)
            ->join($third)
            ->join($fourth);

        $playerPool = $playerPool
            ->leave($first)
            ->leave($second)
            ->leave($third)
            ->leave($fourth);

        $this->assertTrue($playerPool->isEmpty());
    }

    #[Test]
    public function itShouldThrowAnExceptionIfPlayerAlreadyJoined(): void
    {
        $this->expectException(PlayerAlreadyJoinedException::class);

        $playerPool = PlayerPool::beginWith(
            new Player('0')
        );

        $playerPool->join(
            new Player('0')
        );
    }

    #[Test]
    public function itShouldThrowAnExceptionIfPlayerNotJoined(): void
    {
        $this->expectException(PlayerNotJoinedException::class);

        $playerPool = PlayerPool::beginWith(
            new Player('0')
        );

        $playerPool->leave(
            new Player('1')
        );
    }

    #[Test]
    public function itShouldThrowAnExceptionIfPlayerPoolIsEmptyWhenSwitching(): void
    {
        $this->expectException(PlayerPoolIsEmptyException::class);

        $playerPool = PlayerPool::beginWith(
            new Player('0')
        );

        $playerPool = $playerPool->leave(
            new Player('0')
        );

        $playerPool->switch();
    }

    #[Test]
    public function itShouldThrowAnExceptionIfPlayerPoolIsEmptyWhenGetCurrent(): void
    {
        $this->expectException(PlayerPoolIsEmptyException::class);

        $playerPool = PlayerPool::beginWith(
            new Player('0')
        );

        $playerPool = $playerPool->leave(
            new Player('0')
        );

        $playerPool->current();
    }
}
