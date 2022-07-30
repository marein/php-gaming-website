<?php

declare(strict_types=1);

namespace Gaming\Common\DoctrineHeartbeatMiddleware;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;
use Gaming\Common\Scheduler\Scheduler;

final class SchedulePeriodicHeartbeatMiddleware implements Middleware
{
    public function __construct(
        private readonly Scheduler $scheduler
    ) {
    }

    public function wrap(Driver $driver): Driver
    {
        if (php_sapi_name() !== 'cli') {
            return $driver;
        }

        return new ScheduleHeartbeatOnConnectDriver($driver, $this->scheduler);
    }
}
