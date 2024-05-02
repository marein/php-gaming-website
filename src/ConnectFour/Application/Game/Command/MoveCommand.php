<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Command;

use Gaming\Common\Bus\Request;

/**
 * @implements Request<void>
 */
final class MoveCommand implements Request
{
    public function __construct(
        private readonly string $gameId,
        private readonly string $playerId,
        private readonly int $column
    ) {
    }

    public function gameId(): string
    {
        return $this->gameId;
    }

    public function playerId(): string
    {
        return $this->playerId;
    }

    public function column(): int
    {
        return $this->column;
    }
}
