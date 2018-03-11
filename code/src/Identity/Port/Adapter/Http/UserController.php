<?php

namespace Gambling\Identity\Port\Adapter\Http;

use Gambling\Identity\Application\User\Command\ArriveCommand;
use Gambling\Identity\Application\User\Command\SignUpCommand;
use Gambling\Identity\Application\User\UserService;
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

    /**
     * @return JsonResponse
     */
    public function arriveAction(): JsonResponse
    {
        $userId = $this->userService->arrive(
            new ArriveCommand()
        );

        return new JsonResponse([
            'userId' => $userId
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function signUpAction(Request $request): JsonResponse
    {
        $userId = $request->query->get('userId');

        $this->userService->signUp(
            new SignUpCommand(
                $userId,
                $request->request->get('username'),
                $request->request->get('password')
            )
        );

        return new JsonResponse([
            'userId' => $userId
        ]);
    }
}
