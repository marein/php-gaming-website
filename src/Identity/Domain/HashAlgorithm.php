<?php

declare(strict_types=1);

namespace Gaming\Identity\Domain;

interface HashAlgorithm
{
    public function hash(string $password): string;

    public function verify(string $password, string $hash): bool;
}
