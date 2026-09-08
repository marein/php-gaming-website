<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

final class RedirectToLogin implements AuthenticationEntryPointInterface, AccessDeniedHandlerInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $loginRoute,
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return $this->redirectToLogin($request);
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        if ($user instanceof User && $user->isSignedUp) {
            return null;
        }

        return $this->redirectToLogin($request);
    }

    private function redirectToLogin(Request $request): Response
    {
        $loginUrl = $this->urlGenerator->generate($this->loginRoute);

        if ($request->headers->has('HX-Request')) {
            return new Response('', Response::HTTP_UNAUTHORIZED, ['HX-Location' => $loginUrl]);
        }

        return new RedirectResponse($loginUrl);
    }
}
