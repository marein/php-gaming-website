<?php

declare(strict_types=1);

namespace Gaming\Tests\Integration\Common\DoctrineHeartbeatMiddleware;

use DateTimeImmutable;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\Middleware as LoggerMiddleware;
use Gaming\Common\Clock\Clock;
use Gaming\Common\DoctrineHeartbeatMiddleware\SchedulePeriodicHeartbeatMiddleware;
use Gaming\Common\Scheduler\TestScheduler;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;

final class SchedulePeriodicHeartbeatMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldScheduleHeartbeatsUntilClosed(): void
    {
        $scheduler = new TestScheduler();
        $logger = new TestLogger();
        $connection = $this->createConnectionWithMiddleware('sqlite:///:memory:?heartbeat=5', $scheduler, $logger);

        // No connection has been opened yet.
        self::assertSame(0, $scheduler->numberOfPendingJobs());

        // The handler should be scheduled after the connection is opened.
        $connection->executeQuery('SELECT 1');
        self::assertSame(1, $scheduler->numberOfPendingJobs());

        // Reset the logger, as this is used to test whether queries have been made.
        $logger->reset();

        // It should be rescheduled after each invocation.
        for ($i = 0; $i < 10; $i++) {
            $scheduler->invokePendingJobs();
            self::assertSame(1, $scheduler->numberOfPendingJobs());
        }

        // No database calls should've been made.
        self::assertFalse($logger->hasDebugRecords());

        // It should trigger a heartbeat and reschedule after the configured timeout.
        Clock::instance()->freeze(new DateTimeImmutable('+7 seconds'));
        $scheduler->invokePendingJobs();
        self::assertTrue($logger->hasDebugRecords());
        self::assertSame(1, $scheduler->numberOfPendingJobs());
        $logger->reset();
        Clock::instance()->resume();

        // It shouldn't trigger a heartbeat and shouldn't be rescheduled after the connection is closed.
        Clock::instance()->freeze(Clock::instance()->now()->modify('+7 seconds'));
        $connection->close();
        $scheduler->invokePendingJobs();
        self::assertFalse($logger->hasDebugRecords());
        self::assertSame(0, $scheduler->numberOfPendingJobs());
        Clock::instance()->resume();
    }

    /**
     * @test
     * @dataProvider withoutHeartbeatDataProvider
     */
    public function itShouldNotScheduleWithoutHeartbeatConfiguration(string $url): void
    {
        $scheduler = new TestScheduler();
        $connection = $this->createConnectionWithMiddleware($url, $scheduler, new TestLogger());

        $connection->executeQuery('SELECT 1');
        self::assertSame(0, $scheduler->numberOfPendingJobs());
    }

    private function createConnectionWithMiddleware(
        string $url,
        TestScheduler $scheduler,
        TestLogger $logger
    ): Connection {
        return DriverManager::getConnection(
            ['url' => $url],
            (new Configuration())
                ->setMiddlewares(
                    [
                        new LoggerMiddleware($logger),
                        new SchedulePeriodicHeartbeatMiddleware($scheduler)
                    ]
                )
        );
    }

    private function withoutHeartbeatDataProvider(): array
    {
        return [
            ['sqlite:///:memory:'],
            ['sqlite:///:memory:?heartbeat=0'],
            ['sqlite:///:memory:?heartbeat=-1']
        ];
    }
}
