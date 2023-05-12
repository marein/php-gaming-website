<?php

declare(strict_types=1);

namespace Gaming\Common\IdempotentStorage;

use Predis\ClientInterface;

/**
 * @template T
 * @implements IdempotentStorage<T>
 */
final class PredisIdempotentStorage implements IdempotentStorage
{
    public function __construct(
        private readonly ClientInterface $predis,
        private readonly int $expireTimeInSeconds
    ) {
    }

    public function add(string $idempotencyKey, mixed $value): mixed
    {
        $result = $this->predis->setnx($idempotencyKey, serialize($value));
        $this->predis->expire($idempotencyKey, $this->expireTimeInSeconds);

        return $result === 1 ? $value : unserialize(
            (string)$this->predis->get($idempotencyKey)
        );
    }
}
