<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\NilUuid;

final class UserArgumentValueResolver implements ArgumentValueResolverInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === User::class;
    }

    /**
     * @return iterable<UserInterface>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        yield $this->tokenStorage->getToken()?->getUser() ?? new User(
            (new NilUuid())->toRfc4122()
        );
    }
}
