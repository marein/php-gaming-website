<?php
declare(strict_types=1);

namespace Gaming\Identity\Application\User\Command;

final class SignUpCommand
{
    /**
     * @var string
     */
    private $userId;

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
     * @param string $userId
     * @param string $username
     * @param string $password
     */
    public function __construct(string $userId, string $username, string $password)
    {
        $this->userId = $userId;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function userId(): string
    {
        return $this->userId;
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
