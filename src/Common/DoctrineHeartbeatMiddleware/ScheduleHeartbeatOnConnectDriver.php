<?php

declare(strict_types=1);

namespace Gaming\Common\DoctrineHeartbeatMiddleware;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Gaming\Common\Scheduler\Scheduler;
use Psr\Clock\ClockInterface;

final class ScheduleHeartbeatOnConnectDriver extends AbstractDriverMiddleware
{
    public function __construct(
        Driver $driver,
        private readonly Scheduler $scheduler,
        private readonly int $heartbeat,
        private readonly ClockInterface $clock,
    ) {
        parent::__construct($driver);
    }

    public function connect(array $params): TrackActivityConnection
    {
        $trackActivityConnection = new TrackActivityConnection(parent::connect($params));

        $this->scheduler->schedule(
            time() + $this->heartbeat,
            new ConnectionHeartbeatHandler(
                $trackActivityConnection,
                $this->getDatabasePlatform($trackActivityConnection)->getDummySelectSQL(),
                $this->heartbeat,
                $this->clock
            )
        );

        return $trackActivityConnection;
    }
}
