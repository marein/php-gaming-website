<?php

declare(strict_types=1);

namespace Gaming\Identity\Application\User\Query;

final class User
{
    public function __construct(
        public readonly string $userId,
        public readonly string $username,
        public readonly bool $isSignedUp
    ) {
    }
}
