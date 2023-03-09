<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Closure;
use Doctrine\DBAL\Connection;
use Gaming\ConnectFour\Domain\Game\Game;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\Games;
use mysqli;

final class SelectShardGameRepository implements Games
{
    /**
     * @param string[] $shards
     */
    public function __construct(
        private readonly Games $games,
        private readonly Connection $connection,
        private readonly array $shards
    ) {
    }

    public function nextIdentity(): GameId
    {
        return $this->games->nextIdentity();
    }

    public function add(Game $game): void
    {
        $this->selectDatabase($game->id());

        $this->games->add($game);
    }

    public function update(GameId $gameId, Closure $operation): void
    {
        $this->selectDatabase($gameId);

        $this->games->update($gameId, $operation);
    }

    private function selectDatabase(GameId $gameId): void
    {
        $mysqli = $this->connection->getNativeConnection();
        assert($mysqli instanceof mysqli);

        $mysqli->select_db(
            $this->shards[crc32($gameId->toString()) % count($this->shards)]
        );
    }
}
