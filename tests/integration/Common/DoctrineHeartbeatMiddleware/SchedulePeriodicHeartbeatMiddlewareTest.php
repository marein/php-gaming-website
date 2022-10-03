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
use Psr\Log\LoggerInterface;

final class SchedulePeriodicHeartbeatMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldScheduleHeartbeatsUntilClosed(): void
    {
        $scheduler = new TestScheduler();
        $logger = $this->createMock(LoggerInterface::class);
        $connection = $this->createConnectionWithMiddleware('sqlite:///:memory:?heartbeat=5', $scheduler, $logger);

        // The logger is used to check how many queries have been made.
        // The first expectation is when opening, the second is the heartbeat that is triggered.
        $logger->expects($this->exactly(2))->method('debug');

        // No connection has been opened yet.
        self::assertSame(0, $scheduler->numberOfPendingJobs());

        // The handler should be scheduled after the connection is opened.
        $connection->executeQuery('SELECT 1');
        self::assertSame(1, $scheduler->numberOfPendingJobs());

        // It should be rescheduled after each invocation.
        for ($i = 0; $i < 10; $i++) {
            $scheduler->invokePendingJobs();
            self::assertSame(1, $scheduler->numberOfPendingJobs());
        }

        // It should trigger a heartbeat and reschedule after the configured timeout.
        Clock::instance()->freeze(new DateTimeImmutable('+7 seconds'));
        $scheduler->invokePendingJobs();
        self::assertSame(1, $scheduler->numberOfPendingJobs());
        Clock::instance()->resume();

        // It shouldn't trigger a heartbeat and shouldn't be rescheduled after the connection is closed.
        Clock::instance()->freeze(Clock::instance()->now()->modify('+7 seconds'));
        $connection->close();
        $scheduler->invokePendingJobs();
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
        $logger = $this->createMock(LoggerInterface::class);
        $connection = $this->createConnectionWithMiddleware($url, $scheduler, $logger);

        $connection->executeQuery('SELECT 1');
        self::assertSame(0, $scheduler->numberOfPendingJobs());
    }

    private function createConnectionWithMiddleware(
        string $url,
        TestScheduler $scheduler,
        LoggerInterface $logger
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
