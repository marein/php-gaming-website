<?php

declare(strict_types=1);

namespace Gaming\Tests\Integration\Common\DoctrineHeartbeatMiddleware;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\Middleware as LoggerMiddleware;
use Doctrine\DBAL\Tools\DsnParser;
use Gaming\Common\DoctrineHeartbeatMiddleware\SchedulePeriodicHeartbeatMiddleware;
use Gaming\Common\Scheduler\TestScheduler;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Clock\MockClock;

final class SchedulePeriodicHeartbeatMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldScheduleHeartbeatsUntilClosed(): void
    {
        $scheduler = new TestScheduler();
        $clock = new MockClock();
        $logger = $this->createMock(LoggerInterface::class);
        $connection = $this->createConnectionWithMiddleware('pdo-sqlite:///:memory:', $scheduler, 5, $clock, $logger);

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
        $clock->modify('+7 seconds');
        $scheduler->invokePendingJobs();
        self::assertSame(1, $scheduler->numberOfPendingJobs());

        // It shouldn't trigger a heartbeat and shouldn't be rescheduled after the connection is closed.
        $clock->modify('+7 seconds');
        $connection->close();
        $scheduler->invokePendingJobs();
        self::assertSame(0, $scheduler->numberOfPendingJobs());
    }

    /**
     * @test
     * @dataProvider withoutHeartbeatDataProvider
     */
    public function itShouldNotScheduleWithoutHeartbeatConfiguration(string $url, int $heartbeat): void
    {
        $scheduler = new TestScheduler();
        $logger = $this->createMock(LoggerInterface::class);
        $connection = $this->createConnectionWithMiddleware($url, $scheduler, $heartbeat, new MockClock(), $logger);

        $connection->executeQuery('SELECT 1');
        self::assertSame(0, $scheduler->numberOfPendingJobs());
    }

    private function createConnectionWithMiddleware(
        string $url,
        TestScheduler $scheduler,
        int $heartbeat,
        ClockInterface $clock,
        LoggerInterface $logger
    ): Connection {
        return DriverManager::getConnection(
            (new DsnParser())->parse($url),
            (new Configuration())
                ->setMiddlewares(
                    [
                        new LoggerMiddleware($logger),
                        new SchedulePeriodicHeartbeatMiddleware($scheduler, $heartbeat, $clock)
                    ]
                )
        );
    }

    private function withoutHeartbeatDataProvider(): array
    {
        return [
            ['pdo-sqlite:///:memory:', 0],
            ['pdo-sqlite:///:memory:', -1]
        ];
    }
}
