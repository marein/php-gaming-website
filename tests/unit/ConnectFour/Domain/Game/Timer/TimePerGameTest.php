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

        $timer = TimePerGame::set(60);
        $this->assertEquals(60000, $timer->remainingMs);
        $this->assertEquals(null, $timer->endsAt);

        $timer = $timer->start($now);
        $this->assertEquals(60000, $timer->remainingMs);
        $this->assertEquals($now->modify('+60 seconds'), $timer->endsAt);

        $timer = $timer->stop($now = $now->modify('+30 seconds'));
        $this->assertEquals(30000, $timer->remainingMs);
        $this->assertEquals(null, $timer->endsAt);

        $timer = $timer->start($now = $now->modify('+10 seconds'));
        $this->assertEquals(30000, $timer->remainingMs);
        $this->assertEquals($now->modify('+30 seconds'), $timer->endsAt);

        $timer = $timer->stop($now = $now->modify('+29999 milliseconds'));
        $this->assertEquals(1, $timer->remainingMs);
        $this->assertEquals(null, $timer->endsAt);

        $timer = $timer->start($now = $now->modify('+10 seconds'));
        $this->assertEquals(1, $timer->remainingMs);
        $this->assertEquals($now->modify('+1 millisecond'), $timer->endsAt);

        $timer = $timer->stop($now)->start($now);
        $this->expectException(\Exception::class);
        $timer->stop($now->modify('+1 millisecond'));
    }
}
