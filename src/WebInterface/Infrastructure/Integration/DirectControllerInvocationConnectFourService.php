<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\Integration;

use Gaming\ConnectFour\Port\Adapter\Http\GameController;
use Gaming\WebInterface\Application\ConnectFourService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DirectControllerInvocationConnectFourService implements ConnectFourService
{
    private GameController $gameController;

    public function __construct(GameController $gameController)
    {
        $this->gameController = $gameController;
    }

    public function openGames(): array
    {
        return $this->sendRequest('openGames');
    }

    public function runningGames(): array
    {
        return $this->sendRequest('runningGames');
    }

    public function gamesByPlayer(string $playerId): array
    {
        return $this->sendRequest(
            'gamesByPlayer',
            [
                'playerId' => $playerId
            ]
        );
    }

    public function game(string $gameId): array
    {
        return $this->sendRequest(
            'game',
            [
                'gameId' => $gameId
            ]
        );
    }

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

    public function resign(string $gameId, string $playerId): array
    {
        return $this->sendRequest(
            'resign',
            [
                'gameId' => $gameId
            ],
            [
                'playerId' => $playerId
            ]
        );
    }

    public function move(string $gameId, string $playerId, int $column): array
    {
        return $this->sendRequest(
            'move',
            [
                'gameId' => $gameId
            ],
            [
                'playerId' => $playerId,
                'column' => $column
            ]
        );
    }

    /**
     * @param array<string, mixed> $queryParameter
     * @param array<string, mixed> $postParameter
     *
     * @return array<string, mixed>
     */
    private function sendRequest(string $actionName, array $queryParameter = [], array $postParameter = []): array
    {
        $method = $actionName . 'Action';

        $response = $this->gameController->$method(
            new Request($queryParameter, $postParameter)
        );
        assert($response instanceof Response);

        return json_decode((string)$response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }
}
