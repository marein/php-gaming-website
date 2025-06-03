<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game\Timer;

use DateTimeImmutable;
use Gaming\ConnectFour\Domain\Game\Timer\Fischer;
use PHPUnit\Framework\TestCase;

class FischerTest extends TestCase
{
    /**
     * @test
     */
    public function itWorks(): void
    {
        $now = new DateTimeImmutable();

        $timer = Fischer::set(60, 5);
        $this->assertEquals(60000, $timer->remainingMs);
        $this->assertEquals(null, $timer->endsAt);

        $timer = $timer->start($now);
        $this->assertEquals(60000, $timer->remainingMs);
        $this->assertEquals($now->modify('+60 seconds'), $timer->endsAt);

        $timer = $timer->stop($now = $now->modify('+30 seconds'));
        $this->assertEquals(35000, $timer->remainingMs);
        $this->assertEquals(null, $timer->endsAt);

        $timer = $timer->start($now = $now->modify('+10 seconds'));
        $this->assertEquals(35000, $timer->remainingMs);
        $this->assertEquals($now->modify('+35 seconds'), $timer->endsAt);

        $timer = $timer->stop($now = $now->modify('+34999 milliseconds'));
        $this->assertEquals(5001, $timer->remainingMs);
        $this->assertEquals(null, $timer->endsAt);

        $timer = $timer->start($now = $now->modify('+10 seconds'));
        $this->assertEquals(5001, $timer->remainingMs);
        $this->assertEquals($now->modify('+5001 millisecond'), $timer->endsAt);

        $this->expectException(\Exception::class);
        $timer->stop($now->modify('+5001 millisecond'));
    }
}
