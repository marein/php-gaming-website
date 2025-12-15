<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Common\Scheduler;

use Gaming\Common\Scheduler\Handler;
use Gaming\Common\Scheduler\NullHandler;
use Gaming\Common\Scheduler\PcntlScheduler;
use Gaming\Common\Scheduler\Scheduler;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PcntlSchedulerTest extends TestCase
{
    #[Test]
    public function itShouldSchedule(): void
    {
        $expectedCount = 2;

        $testHandler = new class ($expectedCount) implements Handler {
            public function __construct(
                public readonly int $expectedCount,
                public int $count = 0,
            ) {
            }

            public function handle(Scheduler $scheduler): void
            {
                if (++$this->count === $this->expectedCount) {
                    return;
                }

                $scheduler->schedule(time(), $this);
            }
        };

        $scheduler = new PcntlScheduler();

        $scheduler->schedule(
            time(),
            $testHandler
        );

        $scheduler->schedule(
            time(),
            new NullHandler()
        );

        // This test needs to sleep, otherwise the SIGALRM implementation cannot be tested.
        $seconds = 3;
        while ($seconds = sleep($seconds));

        self::assertSame($expectedCount, $testHandler->count);
    }
}
