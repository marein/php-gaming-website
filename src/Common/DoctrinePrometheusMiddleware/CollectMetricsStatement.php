<?php

declare(strict_types=1);

namespace Gaming\Common\DoctrinePrometheusMiddleware;

use Doctrine\DBAL\Driver\Middleware\AbstractStatementMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use Prometheus\RegistryInterface;

final class CollectMetricsStatement extends AbstractStatementMiddleware
{
    private readonly string $normalizedSql;

    public function __construct(
        private readonly Statement $statement,
        private readonly RegistryInterface $registry,
        private readonly string $metricsNamespace,
        string $sql
    ) {
        parent::__construct($this->statement);

        $this->normalizedSql = preg_replace(
            ['/\s+/', '/ IN \([^\)]*\)/'],
            [' ', ' IN (?)'],
            $sql
        ) ?? $sql;
    }

    public function execute(): Result
    {
        $start = microtime(true);

        try {
            return parent::execute();
        } finally {
            $this->registry
                ->getOrRegisterHistogram(
                    $this->metricsNamespace,
                    'sql_query_duration_seconds',
                    'SQL query latencies in seconds.',
                    ['sql'],
                    [0.001, 0.002, 0.003, 0.004, 0.005, 0.0075, 0.01, 0.025, 0.5, 2]
                )->observe(
                    microtime(true) - $start,
                    [$this->normalizedSql]
                );
        }
    }
}
