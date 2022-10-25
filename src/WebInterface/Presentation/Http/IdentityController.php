<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http;

use Gaming\WebInterface\Application\IdentityService;
use Gaming\WebInterface\Infrastructure\Security\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class IdentityController
{
    public function __construct(
        private readonly IdentityService $identityService
    ) {
    }

    public function signUpAction(Request $request, User $user): JsonResponse
    {
        return new JsonResponse(
            $this->identityService->signUp(
                $user->getUserIdentifier(),
                (string)$request->request->get('username', uniqid()),
                (string)$request->request->get('password', 'password')
            )
        );
    }
}
