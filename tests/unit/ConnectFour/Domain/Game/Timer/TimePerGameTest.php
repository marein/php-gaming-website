<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game\Timer;

use DateTimeImmutable;
use Gaming\ConnectFour\Domain\Game\Timer\TimePerGame;
use PHPUnit\Framework\TestCase;

class TimePerGameTest extends TestCase
{
    /**
     * @test
     */
    public function itWorks(): void
    {
        $now = new DateTimeImmutable();
        $nowMs = $now->getTimestamp() * 1000 + (int)($now->getMicrosecond() / 1000);

        $timer = TimePerGame::set(60);
        $this->assertEquals(60000, $timer->remainingMs);
        $this->assertEquals(null, $timer->endsAt);

        $timer = $timer->start($now);
        $this->assertEquals(60000, $timer->remainingMs);
        $this->assertEquals($nowMs + 60000, $timer->endsAt);

        $now = $now->modify('+30 seconds');
        $nowMs += 30000;
        $timer = $timer->stop($now);
        $this->assertEquals(30000, $timer->remainingMs);
        $this->assertEquals(null, $timer->endsAt);

        $now = $now->modify('+10 seconds');
        $nowMs += 10000;
        $timer = $timer->start($now);
        $this->assertEquals(30000, $timer->remainingMs);
        $this->assertEquals($nowMs + 30000, $timer->endsAt);

        $now = $now->modify('+29999 milliseconds');
        $nowMs += 29999;
        $timer = $timer->stop($now);
        $this->assertEquals(1, $timer->remainingMs);
        $this->assertEquals(null, $timer->endsAt);

        $now = $now->modify('+10 seconds');
        $nowMs += 10000;
        $timer = $timer->start($now);
        $this->assertEquals(1, $timer->remainingMs);
        $this->assertEquals($nowMs + 1, $timer->endsAt);

        $timer = $timer->stop($now)->start($now);
        $this->expectException(\Exception::class);
        $timer->stop($now->modify('+1 millisecond'));
    }
}
