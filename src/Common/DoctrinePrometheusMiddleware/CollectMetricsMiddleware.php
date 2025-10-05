<?php

declare(strict_types=1);

namespace Gaming\Common\DoctrinePrometheusMiddleware;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;
use Prometheus\RegistryInterface;

final class CollectMetricsMiddleware implements Middleware
{
    public function __construct(
        private readonly RegistryInterface $registry,
        private readonly string $metricsNamespace
    ) {
    }

    public function wrap(Driver $driver): Driver
    {
        return new CollectMetricsDriver($driver, $this->registry, $this->metricsNamespace);
    }
}
