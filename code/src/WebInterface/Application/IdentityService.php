<?php

namespace Gambling\WebInterface\Application;

interface IdentityService
{
    /**
     * A new user arrives.
     *
     * @return array
     */
    public function arrive(): array;

    /**
     * Sign up a user.
     *
     * @param string $userId
     * @param string $username
     * @param string $password
     *
     * @return array
     */
    public function signUp(string $userId, string $username, string $password): array;
}
