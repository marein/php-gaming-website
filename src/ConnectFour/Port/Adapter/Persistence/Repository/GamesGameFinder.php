<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Gaming\ConnectFour\Application\Game\Query\Model\Game\Game;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\GameFinder;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\Games;

final class GamesGameFinder implements GameFinder
{
    public function __construct(
        private readonly Games $games
    ) {
    }

    public function find(GameId $gameId): Game
    {
        $domainEvents = $this->games->eventsFor($gameId);

        $game = new Game();
        foreach ($domainEvents as $domainEvent) {
            $game->apply($domainEvent);
        }

        return $game;
    }
}
