<?php

declare(strict_types=1);

namespace Gaming\Common\DoctrineHeartbeatMiddleware;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Gaming\Common\Scheduler\Scheduler;

final class ScheduleHeartbeatOnConnectDriver extends AbstractDriverMiddleware
{
    public function __construct(
        Driver $driver,
        private readonly Scheduler $scheduler
    ) {
        parent::__construct($driver);
    }

    public function connect(array $params)
    {
        $interval = (int)($params['heartbeat'] ?? 0);
        if ($interval <= 0) {
            return parent::connect($params);
        }

        $connection = new TrackActivityConnection(parent::connect($params));

        $this->scheduler->schedule(
            time() + $interval,
            new ConnectionHeartbeatHandler(
                $connection,
                $this->getDatabasePlatform()->getDummySelectSQL(),
                $interval
            )
        );

        return $connection;
    }
}
