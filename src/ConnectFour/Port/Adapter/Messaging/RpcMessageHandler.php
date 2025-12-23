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
use Gaming\ConnectFour\Application\Game\Query\Model\OpenGames\OpenGame;
use Gaming\ConnectFour\Application\Game\Query\OpenGamesQuery;
use GamingPlatform\Api\ConnectFour\V1\ConnectFourV1;
use GamingPlatform\Api\ConnectFour\V1\Game as ProtoV1Game;
use GamingPlatform\Api\ConnectFour\V1\GetGamesByPlayer\State as ProtoV1State;

final class RpcMessageHandler implements MessageHandler
{
    public function __construct(
        private readonly Bus $commandBus,
        private readonly Bus $queryBus
    ) {
    }

    public function handle(Message $message, Context $context): void
    {
        match ($message->name()) {
            ConnectFourV1::OpenGameType => $this->handleOpenGame($message, $context),
            ConnectFourV1::JoinGameType => $this->handleJoinGame($message, $context),
            ConnectFourV1::MakeMoveType => $this->handleMakeMove($message, $context),
            ConnectFourV1::GetOpenGamesType => $this->handleGetOpenGames($message, $context),
            ConnectFourV1::GetGamesByPlayerType => $this->handleGetGamesByPlayer($message, $context),
            default => true
        };
    }

    private function handleOpenGame(Message $message, Context $context): void
    {
        $request = ConnectFourV1::createOpenGame($message->body());

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
                ConnectFourV1::OpenGameResponseType,
                ConnectFourV1::createOpenGameResponse()->setGameId($response)->serializeToString()
            )
        );
    }

    private function handleJoinGame(Message $message, Context $context): void
    {
        $request = ConnectFourV1::createJoinGame($message->body());

        $this->commandBus->handle(
            new JoinCommand(
                $request->getGameId(),
                $request->getPlayerId()
            )
        );

        $context->reply(
            new Message(
                ConnectFourV1::JoinGameResponseType,
                ConnectFourV1::createJoinGameResponse()->serializeToString()
            )
        );
    }

    private function handleMakeMove(Message $message, Context $context): void
    {
        $request = ConnectFourV1::createMakeMove($message->body());

        $this->commandBus->handle(
            new MoveCommand(
                $request->getGameId(),
                $request->getPlayerId(),
                $request->getColumn()
            )
        );

        $context->reply(
            new Message(
                ConnectFourV1::MakeMoveResponseType,
                ConnectFourV1::createMakeMoveResponse()->serializeToString()
            )
        );
    }

    private function handleGetOpenGames(Message $message, Context $context): void
    {
        $request = ConnectFourV1::createGetOpenGames($message->body());

        $response = $this->queryBus->handle(
            new OpenGamesQuery(
                $request->getLimit()
            )
        );

        $context->reply(
            new Message(
                ConnectFourV1::GetOpenGamesResponseType,
                ConnectFourV1::createGetOpenGamesResponse()
                    ->setGames(
                        array_map(
                            static fn(OpenGame $game) => ConnectFourV1::createGetOpenGamesResponse_Game()
                                ->setGameId($game->gameId)
                                ->setWidth($game->width)
                                ->setHeight($game->height)
                                ->setPlayerId($game->playerId),
                            $response->games()
                        )
                    )
                    ->serializeToString()
            )
        );
    }

    private function handleGetGamesByPlayer(Message $message, Context $context): void
    {
        $request = ConnectFourV1::createGetGamesByPlayer($message->body());

        $response = $this->queryBus->handle(
            new GamesByPlayerQuery(
                $request->getPlayerId(),
                match ($request->getState()) {
                    ProtoV1State::STATE_OPEN => State::Open,
                    ProtoV1State::STATE_RUNNING => State::Running,
                    ProtoV1State::STATE_WON => State::Won,
                    ProtoV1State::STATE_LOST => State::Lost,
                    ProtoV1State::STATE_DRAWN => State::Drawn,
                    default => State::All
                },
                max(1, $request->getPage()),
                max(1, $request->getLimit())
            )
        );

        $context->reply(
            new Message(
                ConnectFourV1::GetGamesByPlayerResponseType,
                ConnectFourV1::createGetGamesByPlayerResponse()
                    ->setGames($this->castGamesToProtoV1Games($response->games))
                    ->setTotal($response->total)
                    ->serializeToString()
            )
        );
    }

    /**
     * @param Game[] $games
     *
     * @return ProtoV1Game[]
     */
    private function castGamesToProtoV1Games(array $games): array
    {
        return array_map(
            static fn(Game $game) => ConnectFourV1::createGame()
                ->setGameId($game->gameId)
                ->setChatId($game->chatId)
                ->setWidth($game->width)
                ->setHeight($game->height)
                ->setCurrentPlayerId($game->currentPlayerId)
                ->setMoves(
                    array_map(
                        static fn(Move $move) => ConnectFourV1::createGame_Move()
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
