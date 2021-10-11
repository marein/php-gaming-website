<?php

declare(strict_types=1);

namespace Gaming\Identity\Port\Adapter\HashAlgorithm;

use Gaming\Identity\Domain\HashAlgorithm;

/**
 * This class is only meant to be used in unit tests.
 */
final class NotSecureHashAlgorithm implements HashAlgorithm
{
    public function hash(string $value): string
    {
        return 'hashed' . $value;
    }

    public function verify(string $password, string $hash): bool
    {
        return $this->hash($password) === $hash;
    }
}
