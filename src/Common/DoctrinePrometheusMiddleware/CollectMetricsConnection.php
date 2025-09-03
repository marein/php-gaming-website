<?php

declare(strict_types=1);

namespace Gaming\Common\DoctrinePrometheusMiddleware;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Prometheus\RegistryInterface;

final class CollectMetricsConnection extends AbstractConnectionMiddleware
{
    public function __construct(
        Connection $connection,
        private readonly RegistryInterface $registry,
        private readonly string $metricsNamespace
    ) {
        parent::__construct($connection);
    }

    public function prepare(string $sql): CollectMetricsStatement
    {
        return new CollectMetricsStatement(parent::prepare($sql), $this->registry, $this->metricsNamespace, $sql);
    }
}
