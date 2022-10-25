<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http;

use Gaming\WebInterface\Application\ConnectFourService;
use Gaming\WebInterface\Infrastructure\Security\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ConnectFourController
{
    public function __construct(
        private readonly ConnectFourService $connectFourService
    ) {
    }

    public function showAction(string $gameId): JsonResponse
    {
        return new JsonResponse(
            $this->connectFourService->game($gameId)
        );
    }

    public function openAction(Request $request, User $user): JsonResponse
    {
        return new JsonResponse(
            $this->connectFourService->open(
                $user->getUserIdentifier()
            )
        );
    }

    public function joinAction(Request $request, User $user, string $gameId): JsonResponse
    {
        return new JsonResponse(
            $this->connectFourService->join(
                $gameId,
                $user->getUserIdentifier()
            )
        );
    }

    public function abortAction(Request $request, User $user, string $gameId): JsonResponse
    {
        return new JsonResponse(
            $this->connectFourService->abort(
                $gameId,
                $user->getUserIdentifier()
            )
        );
    }

    public function resignAction(Request $request, User $user, string $gameId): JsonResponse
    {
        return new JsonResponse(
            $this->connectFourService->resign(
                $gameId,
                $user->getUserIdentifier()
            )
        );
    }

    public function moveAction(Request $request, User $user, string $gameId): JsonResponse
    {
        return new JsonResponse(
            $this->connectFourService->move(
                $gameId,
                $user->getUserIdentifier(),
                (int)$request->request->get('column', -1)
            )
        );
    }
}
