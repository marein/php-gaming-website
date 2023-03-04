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
        $parameters = new Parameters($params);
        $connection = parent::connect($parameters->removeDriverOptions()->parameters);

        if (php_sapi_name() !== 'cli' || $parameters->heartbeat() <= 0) {
            return $connection;
        }

        $trackActivityConnection = new TrackActivityConnection($connection);

        $this->scheduler->schedule(
            time() + $parameters->heartbeat(),
            new ConnectionHeartbeatHandler(
                $trackActivityConnection,
                $this->getDatabasePlatform()->getDummySelectSQL(),
                $parameters->heartbeat()
            )
        );

        return $trackActivityConnection;
    }
}
