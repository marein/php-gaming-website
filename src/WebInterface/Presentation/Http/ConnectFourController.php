<?php
declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http;

use Gaming\WebInterface\Application\ConnectFourService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ConnectFourController
{
    /**
     * @var ConnectFourService
     */
    private ConnectFourService $connectFourService;

    /**
     * ConnectFourController constructor.
     *
     * @param ConnectFourService $connectFourService
     */
    public function __construct(ConnectFourService $connectFourService)
    {
        $this->connectFourService = $connectFourService;
    }

    /**
     * @param string $gameId
     *
     * @return JsonResponse
     */
    public function showAction(string $gameId): JsonResponse
    {
        return new JsonResponse(
            $this->connectFourService->game($gameId)
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function openAction(Request $request): JsonResponse
    {
        return new JsonResponse(
            $this->connectFourService->open(
                (string)$request->getSession()->get('user')
            )
        );
    }

    /**
     * @param Request $request
     * @param string  $gameId
     *
     * @return JsonResponse
     */
    public function joinAction(Request $request, string $gameId): JsonResponse
    {
        return new JsonResponse(
            $this->connectFourService->join(
                $gameId,
                (string)$request->getSession()->get('user')
            )
        );
    }

    /**
     * @param Request $request
     * @param string  $gameId
     *
     * @return JsonResponse
     */
    public function abortAction(Request $request, string $gameId): JsonResponse
    {
        return new JsonResponse(
            $this->connectFourService->abort(
                $gameId,
                (string)$request->getSession()->get('user')
            )
        );
    }

    /**
     * @param Request $request
     * @param string  $gameId
     *
     * @return JsonResponse
     */
    public function resignAction(Request $request, string $gameId): JsonResponse
    {
        return new JsonResponse(
            $this->connectFourService->resign(
                $gameId,
                (string)$request->getSession()->get('user')
            )
        );
    }

    /**
     * @param Request $request
     * @param string  $gameId
     *
     * @return JsonResponse
     */
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
