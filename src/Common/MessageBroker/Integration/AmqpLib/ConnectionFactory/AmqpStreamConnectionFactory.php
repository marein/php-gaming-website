<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\ConnectionFactory;

use Gaming\Common\MessageBroker\Exception\MessageBrokerException;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Throwable;

final class AmqpStreamConnectionFactory implements ConnectionFactory
{
    public function __construct(
        private readonly string $dsn
    ) {
    }

    public function create(): AbstractConnection
    {
        $urlComponents = parse_url($this->dsn);
        if (!is_array($urlComponents)) {
            throw new MessageBrokerException('Cannot parse ' . $this->dsn . ' as url.');
        }

        $queryParameters = [];
        parse_str($urlComponents['query'] ?? '', $queryParameters);

        try {
            return new AMQPStreamConnection(
                host: $urlComponents['host'] ?? '127.0.0.1',
                port: $urlComponents['port'] ?? 5672,
                user: $urlComponents['user'] ?? 'guest',
                password: $urlComponents['pass'] ?? 'guest',
                vhost: $urlComponents['path'] ?? '/',
                heartbeat: (int)($queryParameters['heartbeat'] ?? 0)
            );
        } catch (Throwable $throwable) {
            throw MessageBrokerException::fromThrowable($throwable);
        }
    }
}
