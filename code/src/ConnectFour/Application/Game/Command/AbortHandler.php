<?php
declare(strict_types=1);

namespace Gambling\ConnectFour\Application\Game\Command;

use Gambling\ConnectFour\Domain\Game\GameId;
use Gambling\ConnectFour\Domain\Game\Games;

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
