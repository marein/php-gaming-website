<?php

declare(strict_types=1);

namespace Gaming\Common\IdempotentStorage;

/**
 * @template T
 */
interface IdempotentStorage
{
    /**
     * The first call with a specific idempotency key will return the value.
     * Any subsequent calls with the same idempotency key will return the value of the first call.
     * Always rely on the return value of this method, never on the passed value.
     *
     * Implementations may decide to expire the idempotency key after a certain amount of time.
     * Implementations must be concurrent safe.
     *
     * @param T $value
     *
     * @return T
     */
    public function add(string $idempotencyKey, mixed $value): mixed;
}
