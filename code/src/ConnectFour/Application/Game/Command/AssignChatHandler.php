<?php

namespace Gambling\ConnectFour\Application\Game\Command;

use Gambling\ConnectFour\Domain\Game\GameId;
use Gambling\ConnectFour\Domain\Game\Games;

final class AssignChatHandler
{
    /**
     * @var Games
     */
    private $games;

    /**
     * JoinHandler constructor.
     *
     * @param Games $games
     */
    public function __construct(Games $games)
    {
        $this->games = $games;
    }

    public function __invoke(AssignChatCommand $command): void
    {
        $game = $this->games->get(GameId::fromString($command->gameId()));

        $game->assignChat($command->chatId());

        $this->games->save($game);
    }
}
