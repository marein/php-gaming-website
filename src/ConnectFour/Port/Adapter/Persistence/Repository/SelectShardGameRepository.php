<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

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

    /**
     * @throw ConcurrencyException
     */
    public function save(Game $game): void
    {
        $this->selectDatabase($game->id());

        $this->games->save($game);
    }

    public function get(GameId $id): Game
    {
        $this->selectDatabase($id);

        return $this->games->get($id);
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
