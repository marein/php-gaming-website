<?php

declare(strict_types=1);

namespace Gaming\Identity\Application\User\Command;

use Gaming\Common\Bus\Request;

/**
 * @implements Request<void>
 */
final class SignUpCommand implements Request
{
    public function __construct(
        private readonly string $userId,
        private readonly string $username,
        private readonly string $password
    ) {
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
