<?php

namespace Gambling\Identity\Port\Adapter\HashAlgorithm;

use Gambling\Identity\Domain\HashAlgorithm;

final class Argon2iHashAlgorithm implements HashAlgorithm
{
    /**
     * @inheritdoc
     */
    public function hash(string $value): string
    {
        return password_hash($value, PASSWORD_ARGON2I);
    }

    /**
     * @inheritdoc
     */
    public function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
