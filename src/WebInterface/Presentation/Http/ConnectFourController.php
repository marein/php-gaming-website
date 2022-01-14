<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http;

use Gaming\WebInterface\Application\ConnectFourService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ConnectFourController
{
    private ConnectFourService $connectFourService;

    public function __construct(ConnectFourService $connectFourService)
    {
        $this->connectFourService = $connectFourService;
    }

    public function showAction(string $gameId): JsonResponse
    {
        return new JsonResponse(
            $this->connectFourService->game($gameId)
        );
    }

    public function openAction(Request $request): JsonResponse
    {
        return new JsonResponse(
            $this->connectFourService->open(
                (string)$request->getSession()->get('user')
            )
        );
    }

    public function joinAction(Request $request, string $gameId): JsonResponse
    {
        return new JsonResponse(
            $this->connectFourService->join(
                $gameId,
                (string)$request->getSession()->get('user')
            )
        );
    }

    public function abortAction(Request $request, string $gameId): JsonResponse
    {
        return new JsonResponse(
            $this->connectFourService->abort(
                $gameId,
                (string)$request->getSession()->get('user')
            )
        );
    }

    public function resignAction(Request $request, string $gameId): JsonResponse
    {
        return new JsonResponse(
            $this->connectFourService->resign(
                $gameId,
                (string)$request->getSession()->get('user')
            )
        );
    }

    public function moveAction(Request $request, string $gameId): JsonResponse
    {
        return new JsonResponse(
            $this->connectFourService->move(
                $gameId,
                (string)$request->getSession()->get('user'),
                (int)$request->request->get('column', -1)
            )
        );
    }
}
