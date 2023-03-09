<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Closure;
use Doctrine\DBAL\Connection;
use Gaming\ConnectFour\Domain\Game\Game;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\Games;
use mysqli;

final class TransactionalGameRepository implements Games
{
    public function __construct(
        private readonly Games $games,
        private readonly Connection $connection
    ) {
    }

    public function nextIdentity(): GameId
    {
        return $this->games->nextIdentity();
    }

    public function add(Game $game): void
    {
        $this->connection->transactional(fn() => $this->games->add($game));
    }

    public function update(GameId $gameId, Closure $operation): void
    {
        $this->connection->transactional(fn() => $this->games->update($gameId, $operation));
    }
}
