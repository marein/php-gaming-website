<?php

namespace Gambling\User\Application\User\Command;

final class SignUpCommand
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * SignUpCommand constructor.
     *
     * @param string $username
     * @param string $password
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function username(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function password(): string
    {
        return $this->password;
    }
}
