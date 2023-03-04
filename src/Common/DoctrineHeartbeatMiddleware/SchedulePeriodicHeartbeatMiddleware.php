<?php

declare(strict_types=1);

namespace Gaming\Common\DoctrineHeartbeatMiddleware;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;
use Gaming\Common\Scheduler\Scheduler;

/**
 * This middleware should be the first to be applied.
 * This ensures that there really is network activity on the connection.
 */
final class SchedulePeriodicHeartbeatMiddleware implements Middleware
{
    public function __construct(
        private readonly Scheduler $scheduler,
        private readonly int $heartbeat
    ) {
    }

    public function wrap(Driver $driver): Driver
    {
        if (php_sapi_name() !== 'cli' || $this->heartbeat <= 0) {
            return $driver;
        }

        return new ScheduleHeartbeatOnConnectDriver($driver, $this->scheduler, $this->heartbeat);
    }
}
