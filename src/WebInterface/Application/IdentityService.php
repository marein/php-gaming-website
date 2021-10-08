<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Application;

interface IdentityService
{
    /**
     * A new user arrives.
     *
     * @return array<string, mixed>
     */
    public function arrive(): array;

    /**
     * Sign up a user.
     *
     * @param string $userId
     * @param string $username
     * @param string $password
     *
     * @return array<string, mixed>
     */
    public function signUp(string $userId, string $username, string $password): array;
}
