<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Command;

final class AbortCommand
{
    private string $gameId;

    private string $playerId;

    public function __construct(string $gameId, string $playerId)
    {
        $this->gameId = $gameId;
        $this->playerId = $playerId;
    }

    public function gameId(): string
    {
        return $this->gameId;
    }

    public function playerId(): string
    {
        return $this->playerId;
    }
}
