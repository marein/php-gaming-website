<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game\Timer;

use DateTimeImmutable;
use Gaming\Common\Timer\GameTimer;
use PHPUnit\Framework\TestCase;

class GameTimerTest extends TestCase
{
    /**
     * @test
     */
    public function itWorks(): void
    {
        $now = new DateTimeImmutable();
        $nowMs = $now->getTimestamp() * 1000 + (int)($now->getMicrosecond() / 1000);

        $timer = GameTimer::set(60000, 5000);
        $this->assertEquals(60000, $timer->remainingMs);
        $this->assertEquals(null, $timer->endsAt);

        $timer = $timer->start($now);
        $this->assertEquals(60000, $timer->remainingMs);
        $this->assertEquals($nowMs + 60000, $timer->endsAt);

        $now = $now->modify('+30 seconds');
        $nowMs += 30000;
        $timer = $timer->stop($now);
        $this->assertEquals(35000, $timer->remainingMs);
        $this->assertEquals(null, $timer->endsAt);

        $now = $now->modify('+10 seconds');
        $nowMs += 10000;
        $timer = $timer->start($now);
        $this->assertEquals(35000, $timer->remainingMs);
        $this->assertEquals($nowMs + 35000, $timer->endsAt);

        $now = $now->modify('+35000 milliseconds');
        $nowMs += 35000;
        $timer = $timer->stop($now);
        $this->assertEquals(0, $timer->remainingMs);
        $this->assertEquals(null, $timer->endsAt);

        $timer = $timer->start($now);
        $this->assertEquals(0, $timer->remainingMs);
        $this->assertEquals($nowMs, $timer->endsAt);

        $timer = $timer->stop($now);
        $this->assertEquals(0, $timer->remainingMs);
        $this->assertEquals(null, $timer->endsAt);
    }
}
