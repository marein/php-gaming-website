<?php

namespace Gambling\WebInterface\Infrastructure\Integration;

use Gambling\ConnectFour\Port\Adapter\Http\GameController;
use Gambling\WebInterface\Application\ConnectFourService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class DirectControllerInvocationConnectFourService implements ConnectFourService
{
    /**
     * @var GameController
     */
    private $gameController;

    /**
     * DirectControllerInvocationConnectFourService constructor.
     *
     * @param GameController $gameController
     */
    public function __construct(GameController $gameController)
    {
        $this->gameController = $gameController;
    }

    /**
     * @inheritdoc
     */
    public function openGames(): array
    {
        return $this->sendRequest('openGames');
    }

    /**
     * @inheritdoc
     */
    public function runningGames(): array
    {
        return $this->sendRequest('runningGames');
    }

    /**
     * @inheritdoc
     */
    public function gamesByPlayer(string $playerId): array
    {
        return $this->sendRequest(
            'gamesByPlayer',
            [
                'playerId' => $playerId
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function game(string $gameId): array
    {
        return $this->sendRequest(
            'game',
            [
                'gameId' => $gameId
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function open(string $playerId): array
    {
        return $this->sendRequest(
            'open',
            [],
            [
                'playerId' => $playerId
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function join(string $gameId, string $playerId): array
    {
        return $this->sendRequest(
            'join',
            [
                'gameId' => $gameId
            ],
            [
                'playerId' => $playerId
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function abort(string $gameId, string $playerId): array
    {
        return $this->sendRequest(
            'abort',
            [
                'gameId' => $gameId
            ],
            [
                'playerId' => $playerId
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function move(string $gameId, string $playerId, int $column): array
    {
        return $this->sendRequest(
            'move',
            [
                'gameId' => $gameId
            ],
            [
                'playerId' => $playerId,
                'column'   => $column
            ]
        );
    }

    /**
     * Make a call to the controller.
     *
     * @param string $actionName
     * @param array  $queryParameter
     * @param array  $postParameter
     *
     * @return array
     */
    private function sendRequest(string $actionName, array $queryParameter = [], array $postParameter = []): array
    {
        $method = $actionName . 'Action';

        /** @var JsonResponse $response */
        $response = $this->gameController->$method(
            new Request($queryParameter, $postParameter)
        );

        return json_decode($response->getContent(), true);
    }
}
