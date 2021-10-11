<?php

declare(strict_types=1);

namespace Gaming\Identity\Port\Adapter\Http;

use Gaming\Common\Bus\Bus;
use Gaming\Identity\Application\User\Command\ArriveCommand;
use Gaming\Identity\Application\User\Command\SignUpCommand;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class UserController
{
    private Bus $commandBus;

    public function __construct(Bus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function arriveAction(): JsonResponse
    {
        $userId = $this->commandBus->handle(
            new ArriveCommand()
        );

        return new JsonResponse(
            [
                'userId' => $userId
            ]
        );
    }

    public function signUpAction(Request $request): JsonResponse
    {
        $userId = (string)$request->query->get('userId');

        $this->commandBus->handle(
            new SignUpCommand(
                $userId,
                (string)$request->request->get('username'),
                (string)$request->request->get('password')
            )
        );

        return new JsonResponse(
            [
                'userId' => $userId
            ]
        );
    }
}
