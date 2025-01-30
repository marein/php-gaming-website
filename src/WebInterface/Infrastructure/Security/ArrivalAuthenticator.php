<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\Security;

use Gaming\Common\Bus\Bus;
use Gaming\Identity\Application\User\Command\ArriveCommand;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class ArrivalAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly Bus $identityCommandBus,
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public function supports(Request $request): bool
    {
        /**
         * A better idea would be to authenticate an anonymous user only when needed.
         * See https://github.com/marein/php-gaming-website/issues/155.
         */
        return !str_starts_with($request->getPathInfo(), '/_fragment')
            && $this->tokenStorage->getToken() === null;
    }

    public function authenticate(Request $request): Passport
    {
        return new SelfValidatingPassport(
            new UserBadge(
                $this->identityCommandBus->handle(new ArriveCommand())
            )
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        throw $exception;
    }
}
