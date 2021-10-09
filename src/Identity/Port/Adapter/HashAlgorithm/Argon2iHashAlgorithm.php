<?php

declare(strict_types=1);

namespace Gaming\Identity\Port\Adapter\HashAlgorithm;

use Gaming\Identity\Domain\HashAlgorithm;

final class Argon2iHashAlgorithm implements HashAlgorithm
{
    public function hash(string $value): string
    {
        return password_hash($value, PASSWORD_ARGON2I);
    }

    public function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
