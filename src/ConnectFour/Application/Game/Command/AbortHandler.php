<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Command;

use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\Games;

final class AbortHandler
{
    /**
     * @var Games
     */
    private $games;

    /**
     * AbortHandler constructor.
     *
     * @param Games $games
     */
    public function __construct(Games $games)
    {
        $this->games = $games;
    }

    public function __invoke(AbortCommand $command): void
    {
        $game = $this->games->get(GameId::fromString($command->gameId()));

        $game->abort($command->playerId());

        $this->games->save($game);
    }
}
