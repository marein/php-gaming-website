<?php
declare(strict_types=1);

namespace Gaming\Identity\Domain;

interface HashAlgorithm
{
    /**
     * Returns the hashed password.
     *
     * @param string $password
     *
     * @return string
     */
    public function hash(string $password): string;

    /**
     * Returns true if the password matches the hash.
     *
     * @param string $password
     * @param string $hash
     *
     * @return bool
     */
    public function verify(string $password, string $hash): bool;
}
