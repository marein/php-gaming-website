<?php
declare(strict_types=1);

namespace Gambling\Identity\Port\Adapter\HashAlgorithm;

use Gambling\Identity\Domain\HashAlgorithm;

/**
 * This class is only meant to be used in unit tests.
 */
final class NotSecureHashAlgorithm implements HashAlgorithm
{
    /**
     * @inheritdoc
     */
    public function hash(string $value): string
    {
        return 'hashed' . $value;
    }

    /**
     * @inheritdoc
     */
    public function verify(string $password, string $hash): bool
    {
        return $this->hash($password) === $hash;
    }
}
