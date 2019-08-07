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
    /**
     * @var Bus
     */
    private $commandBus;

    /**
     * UserController constructor.
     *
     * @param Bus $commandBus
     */
    public function __construct(Bus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @return JsonResponse
     */
    public function arriveAction(): JsonResponse
    {
        $userId = $this->commandBus->handle(
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

        $this->commandBus->handle(
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
