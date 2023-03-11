<?php

declare(strict_types=1);

namespace Gaming\Common\ShardChooser\Integration;

use Doctrine\DBAL\Connection;
use Gaming\Common\ShardChooser\Exception\ShardChooserException;
use Gaming\Common\ShardChooser\Storage;
use Throwable;

final class DoctrineStorage implements Storage
{
    public function __construct(
        private readonly Connection $connection,
        private readonly string $useStatement
    ) {
    }

    public function useShard(string $shard): void
    {
        try {
            $this->connection->executeStatement(
                sprintf($this->useStatement, $this->connection->quoteIdentifier($shard))
            );
        } catch (Throwable $throwable) {
            throw new ShardChooserException(
                $throwable->getMessage(),
                $throwable->getCode(),
                $throwable
            );
        }
    }
}
