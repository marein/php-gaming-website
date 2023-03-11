<?php

declare(strict_types=1);

namespace Gaming\Common\ShardChooser\Integration;

use Doctrine\DBAL\Connection;
use Gaming\Common\ShardChooser\Storage;

final class DoctrineStorage implements Storage
{
    public function __construct(
        private readonly Connection $connection,
        private readonly string $useStatement
    ) {
    }

    public function useShard(string $shard): void
    {
        $this->connection->executeStatement(
            sprintf($this->useStatement, $this->connection->quoteIdentifier($shard))
        );
    }
}
