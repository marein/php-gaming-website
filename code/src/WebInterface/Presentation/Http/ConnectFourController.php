<?php

namespace Gambling\WebInterface\Presentation\Http;

use Gambling\WebInterface\Application\ConnectFourService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ConnectFourController
{
    /**
     * @var ConnectFourService
     */
    private $connectFourService;

    /**
     * ConnectFourController constructor.
     *
     * @param ConnectFourService $connectFourService
     */
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
                $request->getSession()->get('user')
            )
        );
    }

    public function joinAction(Request $request, string $gameId): JsonResponse
    {
        return new JsonResponse(
            $this->connectFourService->join(
                $gameId,
                $request->getSession()->get('user')
            )
        );
    }

    public function abortAction(Request $request, string $gameId): JsonResponse
    {
        return new JsonResponse(
            $this->connectFourService->abort(
                $gameId,
                $request->getSession()->get('user')
            )
        );
    }

    public function moveAction(Request $request, string $gameId): JsonResponse
    {
        return new JsonResponse(
            $this->connectFourService->move(
                $gameId,
                $request->getSession()->get('user'),
                (int)$request->request->get('column', -1)
            )
        );
    }
}
