<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\ConnectionFactory;

use Gaming\Common\MessageBroker\Exception\MessageBrokerException;
use Gaming\Common\MessageBroker\Integration\AmqpLib\Topology\Topology;
use PhpAmqpLib\Connection\AbstractConnection;
use Throwable;

/**
 * This decorator should only be used in long-lived processes.
 * An alternative would be to declare the topology in a separate
 * process, e.g. in the container's entrypoint, or by another department.
 */
final class DeclareTopologyConnectionFactory implements ConnectionFactory
{
    public function __construct(
        private readonly ConnectionFactory $connectionFactory,
        private readonly Topology $topology
    ) {
    }

    public function create(): AbstractConnection
    {
        $connection = $this->connectionFactory->create();

        try {
            $channel = $connection->channel();
            $this->topology->declare($channel);
            $channel->close();
        } catch (Throwable $throwable) {
            throw MessageBrokerException::fromThrowable($throwable);
        }

        return $connection;
    }
}
