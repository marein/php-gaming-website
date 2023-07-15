<?php

declare(strict_types=1);

namespace Gaming\Common\DoctrineHeartbeatMiddleware;

use Closure;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\ParameterType;

final class TrackActivityConnection implements Connection
{
    private bool $isQuerying;

    private int $lastActivity;

    public function __construct(
        private readonly Connection $connection
    ) {
        $this->isQuerying = false;
        $this->lastActivity = 0;
    }

    public function prepare(string $sql): Statement
    {
        return $this->decorate(fn() => $this->connection->prepare($sql));
    }

    public function query(string $sql): Result
    {
        return $this->decorate(fn() => $this->connection->query($sql));
    }

    public function quote($value, $type = ParameterType::STRING): mixed
    {
        return $this->connection->quote($value, $type);
    }

    public function exec(string $sql): int
    {
        return $this->decorate(fn() => $this->connection->exec($sql));
    }

    public function lastInsertId($name = null): string|int|false
    {
        return $this->decorate(fn() => $this->connection->lastInsertId($name));
    }

    public function beginTransaction(): bool
    {
        return $this->decorate(fn() => $this->connection->beginTransaction());
    }

    public function commit(): bool
    {
        return $this->decorate(fn() => $this->connection->commit());
    }

    public function rollBack(): bool
    {
        return $this->decorate(fn() => $this->connection->rollBack());
    }

    public function isQuerying(): bool
    {
        return $this->isQuerying;
    }

    public function lastActivity(): int
    {
        return $this->lastActivity;
    }

    /**
     * @return resource|object
     */
    public function getNativeConnection()
    {
        return $this->connection->getNativeConnection();
    }

    private function decorate(Closure $action): mixed
    {
        $this->isQuerying = true;

        try {
            return $action();
        } finally {
            $this->lastActivity = time();
            $this->isQuerying = false;
        }
    }
}
