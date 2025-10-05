<?php

declare(strict_types=1);

namespace Gaming\Common\DoctrinePrometheusMiddleware;

use Closure;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Result;
use Prometheus\RegistryInterface;

final class CollectMetricsConnection extends AbstractConnectionMiddleware
{
    public function __construct(
        private readonly Connection $connection,
        private readonly RegistryInterface $registry,
        private readonly string $metricsNamespace
    ) {
        parent::__construct($connection);
    }

    public function prepare(string $sql): CollectMetricsStatement
    {
        return new CollectMetricsStatement(
            $this->connection->prepare($sql),
            $this->observeSql(...),
            $sql
        );
    }

    public function query(string $sql): Result
    {
        return $this->observeSql(fn () => $this->connection->query($sql), $sql);
    }

    public function exec(string $sql): int|string
    {
        return $this->observeSql(fn () => $this->connection->exec($sql), $sql);
    }

    public function beginTransaction(): void
    {
        $this->observeSql($this->connection->beginTransaction(...), 'BEGIN');
    }

    public function commit(): void
    {
        $this->observeSql($this->connection->commit(...), 'COMMIT');
    }

    public function rollBack(): void
    {
        $this->observeSql($this->connection->rollBack(...), 'ROLLBACK');
    }

    private function observeSql(Closure $execution, string $sql): mixed
    {
        $normalizedSql = preg_replace(
            ['/\s+/', '/ IN \([^\)]*\)/'],
            [' ', ' IN (?)'],
            $sql
        ) ?? $sql;

        $start = microtime(true);

        try {
            return $execution();
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
                    [$normalizedSql]
                );
        }
    }
}
