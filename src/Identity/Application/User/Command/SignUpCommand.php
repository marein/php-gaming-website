<?php

declare(strict_types=1);

namespace Gaming\Identity\Application\User\Command;

final class SignUpCommand
{
    private string $userId;

    private string $username;

    private string $password;

    public function __construct(string $userId, string $username, string $password)
    {
        $this->userId = $userId;
        $this->username = $username;
        $this->password = $password;
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function username(): string
    {
        return $this->username;
    }

    public function password(): string
    {
        return $this->password;
    }
}
