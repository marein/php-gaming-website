<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\Security;

use DateTimeImmutable;
use Symfony\Component\Security\Core\User\UserInterface;

final class User implements UserInterface
{
    public function __construct(
        private readonly string $userIdentifier,
        public readonly string $username = '',
        public readonly bool $isSignedUp = false,
        public readonly DateTimeImmutable $refreshAt = new DateTimeImmutable()
    ) {
    }

    public function getRoles(): array
    {
        return [];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }
}
