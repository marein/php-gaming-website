<?php

namespace Gambling\WebInterface\Application;

interface UserService
{
    /**
     * Sign up a user.
     *
     * @param string $username
     * @param string $password
     *
     * @return array
     */
    public function signUp(string $username, string $password): array;
}
