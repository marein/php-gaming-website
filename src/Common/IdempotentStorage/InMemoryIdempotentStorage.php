<?php

declare(strict_types=1);

namespace Gaming\Common\IdempotentStorage;

/**
 * @template T
 * @implements IdempotentStorage<T>
 */
final class InMemoryIdempotentStorage implements IdempotentStorage
{
    /**
     * @var T[]
     */
    private array $storage = [];

    public function add(string $idempotencyKey, mixed $value): mixed
    {
        return $this->storage[$idempotencyKey] ??= $value;
    }
}
