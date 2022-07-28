<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\ConnectionFactory;

use Gaming\Common\Scheduler\Handler;
use Gaming\Common\Scheduler\Scheduler;
use PhpAmqpLib\Connection\AbstractConnection;

final class SchedulePeriodicHeartbeatConnectionFactory implements ConnectionFactory
{
    public function __construct(
        private readonly ConnectionFactory $connectionFactory,
        private readonly Scheduler $scheduler
    ) {
    }

    public function create(): AbstractConnection
    {
        $connection = $this->connectionFactory->create();
        if (!$this->shouldSchedule($connection)) {
            return $connection;
        }

        $interval = (int)ceil($connection->getHeartbeat() / 2);

        $this->scheduler->schedule($interval, $this->createHandler($connection, $interval));

        return $connection;
    }

    private function shouldSchedule(AbstractConnection $connection): bool
    {
        return php_sapi_name() === 'cli' && $connection->getHeartbeat() > 0;
    }

    private function createHandler(AbstractConnection $connection, int $interval): Handler
    {
        return new class ($connection, $interval) implements Handler {
            public function __construct(
                private readonly AbstractConnection $connection,
                private readonly int $interval
            ) {
            }

            public function handle(Scheduler $scheduler): void
            {
                if (!$this->connection->isConnected()) {
                    return;
                }

                if (
                    !$this->connection->isWriting()
                    && ($this->connection->getLastActivity() + $this->interval) < time()
                ) {
                    $this->connection->checkHeartBeat();
                }

                $scheduler->schedule(time() + $this->interval, $this);
            }
        };
    }
}
