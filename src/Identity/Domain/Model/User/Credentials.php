<?php

declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\User;

use Gaming\Identity\Domain\HashAlgorithm;

final class Credentials
{
    private string $username;

    private string $password;

    public function __construct(string $username, string $password, HashAlgorithm $hashAlgorithm)
    {
        $this->username = $username;
        $this->password = $hashAlgorithm->hash($password);
    }

    public function username(): string
    {
        return $this->username;
    }

    public function matches(string $password, HashAlgorithm $hashAlgorithm): bool
    {
        return $hashAlgorithm->verify($password, $this->password);
    }
}
