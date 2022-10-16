<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\Security;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\NilUuid;

final class Security
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public function getUser(): UserInterface
    {
        return $this->tokenStorage->getToken()?->getUser() ?? new User(
            (new NilUuid())->toRfc4122()
        );
    }
}
