<?php

namespace Gambling\WebInterface\Presentation\Http;

use Gambling\WebInterface\Application\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class UserController
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * UserController constructor.
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function signUpAction(Request $request): JsonResponse
    {
        return new JsonResponse(
            $this->userService->signUp(
                $request->request->get('username', uniqid()),
                $request->request->get('password', 'password')
            )
        );
    }
}
