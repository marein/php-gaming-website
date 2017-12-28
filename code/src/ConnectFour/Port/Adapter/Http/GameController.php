<?php

namespace Gambling\ConnectFour\Port\Adapter\Http;

use Gambling\Common\Bus\Bus;
use Gambling\ConnectFour\Application\Game\Command\AbortCommand;
use Gambling\ConnectFour\Application\Game\Command\JoinCommand;
use Gambling\ConnectFour\Application\Game\Command\MoveCommand;
use Gambling\ConnectFour\Application\Game\Command\OpenCommand;
use Gambling\ConnectFour\Application\Game\Query\Exception\GameNotFoundException;
use Gambling\ConnectFour\Application\Game\Query\GameQuery;
use Gambling\ConnectFour\Application\Game\Query\GamesByPlayerQuery;
use Gambling\ConnectFour\Application\Game\Query\Model\Game\Move;
use Gambling\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GameByPlayer;
use Gambling\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GamesByPlayer;
use Gambling\ConnectFour\Application\Game\Query\Model\OpenGames\OpenGame;
use Gambling\ConnectFour\Application\Game\Query\Model\OpenGames\OpenGames;
use Gambling\ConnectFour\Application\Game\Query\Model\RunningGames\RunningGames;
use Gambling\ConnectFour\Application\Game\Query\OpenGamesQuery;
use Gambling\ConnectFour\Application\Game\Query\RunningGamesQuery;
use Gambling\ConnectFour\Application\Game\Query\ShowQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GameController
{
    /**
     * @var Bus
     */
    private $commandBus;

    /**
     * @var Bus
     */
    private $queryBus;

    public function __construct(
        Bus $commandBus,
        Bus $queryBus
    ) {
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function openGamesAction(Request $request): JsonResponse
    {
        /** @var OpenGames $openGames */
        $openGames = $this->queryBus->handle(new OpenGamesQuery());

        $games = array_map(function (OpenGame $openGame) {
            return [
                'gameId'   => $openGame->gameId(),
                'playerId' => $openGame->playerId()
            ];
        }, $openGames->games());

        return new JsonResponse([
            'games' => $games
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function runningGamesAction(Request $request): JsonResponse
    {
        /** @var RunningGames $runningGames */
        $runningGames = $this->queryBus->handle(new RunningGamesQuery());

        return new JsonResponse([
            'count' => $runningGames->count()
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function gamesByPlayerAction(Request $request): JsonResponse
    {
        /** @var GamesByPlayer $gamesByPlayer */
        $gamesByPlayer = $this->queryBus->handle(
            new GamesByPlayerQuery(
                $request->query->get('playerId')
            )
        );

        $games = array_map(function (GameByPlayer $openGame) {
            return $openGame->gameId();
        }, $gamesByPlayer->games());

        return new JsonResponse([
            'games' => $games
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function gameAction(Request $request): JsonResponse
    {
        try {
            $game = $this->queryBus->handle(
                new GameQuery(
                    $request->query->get('gameId')
                )
            );

            return new JsonResponse([
                'gameId'   => $game->id(),
                'chatId'   => $game->chatId(),
                'finished' => $game->finished(),
                'height'   => $game->height(),
                'width'    => $game->width(),
                'moves'    => array_map(function (Move $move) {
                    return [
                        'x'     => $move->x(),
                        'y'     => $move->y(),
                        'color' => $move->color()
                    ];
                }, $game->moves())
            ]);
        } catch (GameNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), $exception);
        }
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function openAction(Request $request): JsonResponse
    {
        $gameId = $this->commandBus->handle(
            new OpenCommand(
                $request->request->get('playerId')
            )
        );

        return new JsonResponse([
            'gameId' => $gameId
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function joinAction(Request $request): JsonResponse
    {
        $gameId = $request->query->get('gameId');

        $this->commandBus->handle(
            new JoinCommand(
                $gameId,
                $request->request->get('playerId')
            )
        );

        return new JsonResponse([
            'gameId' => $gameId
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function abortAction(Request $request): JsonResponse
    {
        $gameId = $request->query->get('gameId');

        $this->commandBus->handle(
            new AbortCommand(
                $gameId,
                $request->request->get('playerId')
            )
        );

        return new JsonResponse([
            'gameId' => $gameId
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function moveAction(Request $request): JsonResponse
    {
        $gameId = $request->query->get('gameId');

        $this->commandBus->handle(
            new MoveCommand(
                $gameId,
                $request->request->get('playerId'),
                (int)$request->request->get('column')
            )
        );

        return new JsonResponse([
            'gameId' => $gameId
        ]);
    }
}
