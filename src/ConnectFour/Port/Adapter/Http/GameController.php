<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Http;

use Gaming\Common\Bus\Bus;
use Gaming\ConnectFour\Application\Game\Command\AbortCommand;
use Gaming\ConnectFour\Application\Game\Command\JoinCommand;
use Gaming\ConnectFour\Application\Game\Command\MoveCommand;
use Gaming\ConnectFour\Application\Game\Command\OpenCommand;
use Gaming\ConnectFour\Application\Game\Command\ResignCommand;
use Gaming\ConnectFour\Application\Game\Query\GameQuery;
use Gaming\ConnectFour\Application\Game\Query\GamesByPlayerQuery;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\Game;
use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GameByPlayer;
use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GamesByPlayer;
use Gaming\ConnectFour\Application\Game\Query\Model\OpenGames\OpenGame;
use Gaming\ConnectFour\Application\Game\Query\Model\OpenGames\OpenGames;
use Gaming\ConnectFour\Application\Game\Query\Model\RunningGames\RunningGames;
use Gaming\ConnectFour\Application\Game\Query\OpenGamesQuery;
use Gaming\ConnectFour\Application\Game\Query\RunningGamesQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class GameController
{
    private Bus $commandBus;

    private Bus $queryBus;

    public function __construct(
        Bus $commandBus,
        Bus $queryBus
    ) {
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
    }

    public function openGamesAction(Request $request): JsonResponse
    {
        $openGames = $this->queryBus->handle(new OpenGamesQuery());
        assert($openGames instanceof OpenGames);

        $games = array_map(
            static fn(OpenGame $openGame): array => [
                'gameId' => $openGame->gameId(),
                'playerId' => $openGame->playerId()
            ],
            $openGames->games()
        );

        return new JsonResponse(
            [
                'games' => $games
            ]
        );
    }

    public function runningGamesAction(Request $request): JsonResponse
    {
        $runningGames = $this->queryBus->handle(new RunningGamesQuery());
        assert($runningGames instanceof RunningGames);

        return new JsonResponse(
            [
                'count' => $runningGames->count()
            ]
        );
    }

    public function gamesByPlayerAction(Request $request): JsonResponse
    {
        $gamesByPlayer = $this->queryBus->handle(
            new GamesByPlayerQuery(
                (string)$request->query->get('playerId')
            )
        );
        assert($gamesByPlayer instanceof GamesByPlayer);

        $games = array_map(
            static fn(GameByPlayer $openGame): string => $openGame->gameId(),
            $gamesByPlayer->games()
        );

        return new JsonResponse(
            [
                'games' => $games
            ]
        );
    }

    public function gameAction(Request $request): JsonResponse
    {
        $game = $this->queryBus->handle(
            new GameQuery(
                (string)$request->query->get('gameId')
            )
        );
        assert($game instanceof Game);

        return new JsonResponse($game);
    }

    public function openAction(Request $request): JsonResponse
    {
        $gameId = $this->commandBus->handle(
            new OpenCommand(
                (string)$request->request->get('playerId')
            )
        );

        return new JsonResponse(
            [
                'gameId' => $gameId
            ]
        );
    }

    public function joinAction(Request $request): JsonResponse
    {
        $gameId = (string)$request->query->get('gameId');

        $this->commandBus->handle(
            new JoinCommand(
                $gameId,
                (string)$request->request->get('playerId')
            )
        );

        return new JsonResponse(
            [
                'gameId' => $gameId
            ]
        );
    }

    public function abortAction(Request $request): JsonResponse
    {
        $gameId = (string)$request->query->get('gameId');

        $this->commandBus->handle(
            new AbortCommand(
                $gameId,
                (string)$request->request->get('playerId')
            )
        );

        return new JsonResponse(
            [
                'gameId' => $gameId
            ]
        );
    }

    public function resignAction(Request $request): JsonResponse
    {
        $gameId = (string)$request->query->get('gameId');

        $this->commandBus->handle(
            new ResignCommand(
                $gameId,
                (string)$request->request->get('playerId')
            )
        );

        return new JsonResponse(
            [
                'gameId' => $gameId
            ]
        );
    }

    public function moveAction(Request $request): JsonResponse
    {
        $gameId = (string)$request->query->get('gameId');

        $this->commandBus->handle(
            new MoveCommand(
                $gameId,
                (string)$request->request->get('playerId'),
                (int)$request->request->get('column')
            )
        );

        return new JsonResponse(
            [
                'gameId' => $gameId
            ]
        );
    }
}
