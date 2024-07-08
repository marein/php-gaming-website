<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http;

use Gaming\WebInterface\Application\IdentityService;
use Gaming\WebInterface\Infrastructure\Security\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class IdentityController
{
    public function __construct(
        private readonly IdentityService $identityService,
        private readonly Security $security
    ) {
    }

    public function signUpAction(Request $request): JsonResponse
    {
        return new JsonResponse(
            $this->identityService->signUp(
                $this->security->getUser()->getUserIdentifier(),
                (string)$request->request->get('email'),
                (string)$request->request->get('username')
            )
        );
    }
}
