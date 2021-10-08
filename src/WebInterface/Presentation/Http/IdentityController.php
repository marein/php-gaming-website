<?php
declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http;

use Gaming\WebInterface\Application\IdentityService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class IdentityController
{
    /**
     * @var IdentityService
     */
    private IdentityService $identityService;

    /**
     * IdentityController constructor.
     *
     * @param IdentityService $identityService
     */
    public function __construct(IdentityService $identityService)
    {
        $this->identityService = $identityService;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function signUpAction(Request $request): JsonResponse
    {
        return new JsonResponse(
            $this->identityService->signUp(
                (string)$request->getSession()->get('user'),
                (string)$request->request->get('username', uniqid()),
                (string)$request->request->get('password', 'password')
            )
        );
    }
}
