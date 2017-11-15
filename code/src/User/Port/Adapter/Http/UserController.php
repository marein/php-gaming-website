<?php

namespace Gambling\User\Port\Adapter\Http;

use Gambling\User\Application\User\Command\SignUpCommand;
use Gambling\User\Application\User\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class UserController
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * UserService constructor.
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function signUpAction(Request $request): JsonResponse
    {
        $userId = $this->userService->signUp(
            new SignUpCommand(
                $request->request->get('username'),
                $request->request->get('password')
            )
        );

        return new JsonResponse([
            'userId' => $userId
        ]);
    }
}
