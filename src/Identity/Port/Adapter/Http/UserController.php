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
    public function __construct(
        private readonly Bus $commandBus
    ) {
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
                (string)$request->request->get('email'),
                (string)$request->request->get('username'),
                $request->request->getBoolean('dryRun')
            )
        );

        return new JsonResponse(
            [
                'userId' => $userId
            ]
        );
    }
}
