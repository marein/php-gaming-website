<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http;

use Gaming\WebInterface\Application\ConnectFourService;
use Gaming\WebInterface\Infrastructure\Security\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ConnectFourController
{
    public function __construct(
        private readonly ConnectFourService $connectFourService,
        private readonly Security $security
    ) {
    }

    public function openAction(Request $request): JsonResponse
    {
        return new JsonResponse(
            $this->connectFourService->open(
                $this->security->getUser()->getUserIdentifier()
            )
        );
    }

    public function joinAction(Request $request, string $gameId): JsonResponse
    {
        return new JsonResponse(
            $this->connectFourService->join(
                $gameId,
                $this->security->getUser()->getUserIdentifier()
            )
        );
    }

    public function abortAction(Request $request, string $gameId): JsonResponse
    {
        return new JsonResponse(
            $this->connectFourService->abort(
                $gameId,
                $this->security->getUser()->getUserIdentifier()
            )
        );
    }

    public function resignAction(Request $request, string $gameId): JsonResponse
    {
        return new JsonResponse(
            $this->connectFourService->resign(
                $gameId,
                $this->security->getUser()->getUserIdentifier()
            )
        );
    }

    public function moveAction(Request $request, string $gameId): JsonResponse
    {
        return new JsonResponse(
            $this->connectFourService->move(
                $gameId,
                $this->security->getUser()->getUserIdentifier(),
                (int)$request->request->get('column', -1)
            )
        );
    }
}
