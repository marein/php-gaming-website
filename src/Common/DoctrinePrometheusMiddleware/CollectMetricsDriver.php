<?php

declare(strict_types=1);

namespace Gaming\Common\DoctrinePrometheusMiddleware;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Prometheus\RegistryInterface;

final class CollectMetricsDriver extends AbstractDriverMiddleware
{
    public function __construct(
        Driver $driver,
        private readonly RegistryInterface $registry,
        private readonly string $metricsNamespace
    ) {
        parent::__construct($driver);
    }

    public function connect(array $params): CollectMetricsConnection
    {
        return new CollectMetricsConnection(parent::connect($params), $this->registry, $this->metricsNamespace);
    }
}
