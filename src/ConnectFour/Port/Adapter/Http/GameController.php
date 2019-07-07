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
    /**
     * @var Bus
     */
    private $commandBus;

    /**
     * @var Bus
     */
    private $queryBus;

    /**
     * GameController constructor.
     *
     * @param Bus $commandBus
     * @param Bus $queryBus
     */
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
        /** @var Game $game */
        $game = $this->queryBus->handle(
            new GameQuery(
                $request->query->get('gameId')
            )
        );

        return new JsonResponse($game);
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
    public function resignAction(Request $request): JsonResponse
    {
        $gameId = $request->query->get('gameId');

        $this->commandBus->handle(
            new ResignCommand(
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
