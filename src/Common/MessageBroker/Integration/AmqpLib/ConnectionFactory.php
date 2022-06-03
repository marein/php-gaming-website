<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib;

use Gaming\Common\MessageBroker\Exception\MessageBrokerException;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Throwable;

final class ConnectionFactory
{
    public function __construct(
        private readonly string $dsn
    ) {
    }

    /**
     * @throws MessageBrokerException
     */
    public function create(): AbstractConnection
    {
        $urlComponents = parse_url($this->dsn);
        if (!is_array($urlComponents)) {
            throw new MessageBrokerException('Cannot parse ' . $this->dsn . ' as url.');
        }

        try {
            return new AMQPStreamConnection(
                $urlComponents['host'] ?? '127.0.0.1',
                $urlComponents['port'] ?? 5672,
                $urlComponents['user'] ?? 'guest',
                $urlComponents['pass'] ?? 'guest',
                $urlComponents['path'] ?? '/'
            );
        } catch (Throwable $throwable) {
            throw MessageBrokerException::fromThrowable($throwable);
        }
    }
}
