<?php

declare(strict_types=1);

namespace Gaming\Common\DoctrinePrometheusMiddleware;

use Closure;
use Doctrine\DBAL\Driver\Middleware\AbstractStatementMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;

final class CollectMetricsStatement extends AbstractStatementMiddleware
{
    /**
     * @param Closure(Closure, string): mixed $observeSql
     */
    public function __construct(
        private readonly Statement $statement,
        private readonly Closure $observeSql,
        private readonly string $sql
    ) {
        parent::__construct($this->statement);
    }

    public function execute(): Result
    {
        return ($this->observeSql)(parent::execute(...), $this->sql);
    }
}
