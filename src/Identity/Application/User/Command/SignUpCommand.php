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
        public readonly string $userId,
        public readonly string $email,
        public readonly string $username
    ) {
    }
}
