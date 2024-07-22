<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\Security;

use Symfony\Bundle\SecurityBundle\Security as SymfonySecurity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Uid\NilUuid;

final class Security
{
    /**
     * @param UserProviderInterface<User> $userProvider
     */
    public function __construct(
        private readonly SymfonySecurity $security,
        private readonly UserProviderInterface $userProvider
    ) {
    }

    public function getUser(): UserInterface
    {
        return $this->security->getUser() ?? new User(
            (new NilUuid())->toRfc4122()
        );
    }

    public function login(string $identifier): ?Response
    {
        return $this->security->login(
            $this->userProvider->loadUserByIdentifier($identifier)
        );
    }
}
