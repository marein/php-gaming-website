<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Command;

final class MoveCommand
{
    private string $gameId;

    private string $playerId;

    private int $column;

    public function __construct(string $gameId, string $playerId, int $column)
    {
        $this->gameId = $gameId;
        $this->playerId = $playerId;
        $this->column = $column;
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
