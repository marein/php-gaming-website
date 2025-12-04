<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Messaging;

use Gaming\Common\Bus\Bus;
use Gaming\Common\MessageBroker\Context;
use Gaming\Common\MessageBroker\Message;
use Gaming\Common\MessageBroker\MessageHandler;
use Gaming\ConnectFour\Application\Game\Command\JoinCommand;
use Gaming\ConnectFour\Application\Game\Command\MoveCommand;
use Gaming\ConnectFour\Application\Game\Command\OpenCommand;
use Gaming\ConnectFour\Application\Game\Query\GamesByPlayerQuery;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\Game;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\Move;
use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\State;
use GamingPlatform\Api\ConnectFour\V1\ConnectFourV1Factory;
use GamingPlatform\Api\ConnectFour\V1\Game as ProtoGame;
use GamingPlatform\Api\ConnectFour\V1\Game\Move as ProtoMove;
use GamingPlatform\Api\ConnectFour\V1\GetGamesByPlayerResponse;
use GamingPlatform\Api\ConnectFour\V1\JoinGameResponse;
use GamingPlatform\Api\ConnectFour\V1\MakeMoveResponse;
use GamingPlatform\Api\ConnectFour\V1\OpenGameResponse;

final class GameRequestsMessageHandler implements MessageHandler
{
    public function __construct(
        private readonly Bus $commandBus,
        private readonly Bus $queryBus
    ) {
    }

    public function handle(Message $message, Context $context): void
    {
        match ($message->name()) {
            'ConnectFour.OpenGame' => $this->handleOpenGame($message, $context),
            'ConnectFour.JoinGame' => $this->handleJoinGame($message, $context),
            'ConnectFour.MakeMove' => $this->handleMakeMove($message, $context),
            'ConnectFour.GetGamesByPlayer' => $this->handleGetGamesByPlayer($message, $context),
            default => true
        };
    }

    private function handleOpenGame(Message $message, Context $context): void
    {
        $request = ConnectFourV1Factory::createOpenGame($message->body());

        $response = $this->commandBus->handle(
            new OpenCommand(
                $request->getPlayerId(),
                $request->getWidth(),
                $request->getHeight(),
                $request->getStone(),
                $request->getTimer()
            )
        );

        $context->reply(
            new Message(
                'ConnectFour.OpenGameResponse',
                new OpenGameResponse()->setGameId($response)->serializeToString()
            )
        );
    }

    private function handleJoinGame(Message $message, Context $context): void
    {
        $request = ConnectFourV1Factory::createJoinGame($message->body());

        $this->commandBus->handle(
            new JoinCommand(
                $request->getGameId(),
                $request->getPlayerId()
            )
        );

        $context->reply(
            new Message(
                'ConnectFour.JoinGameResponse',
                new JoinGameResponse()->serializeToString()
            )
        );
    }

    private function handleMakeMove(Message $message, Context $context): void
    {
        $request = ConnectFourV1Factory::createMakeMove($message->body());

        $this->commandBus->handle(
            new MoveCommand(
                $request->getGameId(),
                $request->getPlayerId(),
                $request->getColumn()
            )
        );

        $context->reply(
            new Message(
                'ConnectFour.MakeMoveResponse',
                new MakeMoveResponse()->serializeToString()
            )
        );
    }

    private function handleGetGamesByPlayer(Message $message, Context $context): void
    {
        $request = ConnectFourV1Factory::createGetGamesByPlayer($message->body());

        $response = $this->queryBus->handle(
            new GamesByPlayerQuery(
                $request->getPlayerId(),
                State::tryFrom($request->getState()) ?? State::ALL,
                $request->getPage(),
                $request->getLimit()
            )
        );

        $context->reply(
            new Message(
                'ConnectFour.GetGamesByPlayerResponse',
                new GetGamesByPlayerResponse()
                    ->setGames($this->castGamesToProtoGames($response->games))
                    ->setTotal($response->total)
                    ->serializeToString()
            )
        );
    }

    /**
     * @param Game[] $games
     *
     * @return ProtoGame[]
     */
    private function castGamesToProtoGames(array $games): array
    {
        return array_map(
            static fn(Game $game) => new ProtoGame()
                ->setGameId($game->gameId)
                ->setCurrentPlayerId($game->currentPlayerId)
                ->setMoves(
                    array_map(
                        static fn(Move $move) => new ProtoMove()
                            ->setColor($move->color)
                            ->setX($move->x)
                            ->setY($move->y),
                        $game->moves
                    )
                ),
            $games
        );
    }
}
