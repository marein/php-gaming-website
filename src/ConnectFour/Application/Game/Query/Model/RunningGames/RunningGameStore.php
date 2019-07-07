<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\Model\RunningGames;

interface RunningGameStore
{
    /**
     * This operation is idempotent.
     */
    public function add(string $gameId): void;

    /**
     * This operation is idempotent.
     */
    public function remove(string $gameId): void;

    /**
     * @return int
     */
    public function count(): int;
}
