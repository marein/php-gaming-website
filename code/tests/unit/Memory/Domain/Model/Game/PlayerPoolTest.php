<?php
declare(strict_types=1);

namespace Gambling\Memory\Domain\Model\Game;

use Gambling\Memory\Domain\Model\Game\Exception\PlayerAlreadyJoinedException;
use PHPUnit\Framework\TestCase;

class PlayerPoolTest extends TestCase
{
    /**
     * @test
     */
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

    /**
     * @test
     */
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
}
