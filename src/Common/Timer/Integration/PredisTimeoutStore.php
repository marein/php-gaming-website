<?php

declare(strict_types=1);

namespace Gaming\Common\Timer\Integration;

use Gaming\Common\Timer\TimeoutStore;
use Predis\ClientInterface;

final class PredisTimeoutStore implements TimeoutStore
{
    public function __construct(
        private readonly ClientInterface $predis,
        private readonly string $storageKey
    ) {
    }

    public function add(array $timeouts): void
    {
        if (count($timeouts) === 0) {
            return;
        }

        $this->predis->zadd($this->storageKey, $timeouts);
    }

    public function remove(array $timeoutIds): void
    {
        if (count($timeoutIds) === 0) {
            return;
        }

        $this->predis->zrem($this->storageKey, ...$timeoutIds);
    }

    public function find(int $maxTimeMs, int $limit = 1000): array
    {
        return $this->predis->zrangebyscore(
            $this->storageKey,
            0,
            $maxTimeMs,
            ['limit' => [0, $limit], 'withscores' => true]
        );
    }
}
