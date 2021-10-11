<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Command;

final class OpenCommand
{
    private string $playerId;

    public function __construct(string $playerId)
    {
        $this->playerId = $playerId;
    }

    public function playerId(): string
    {
        return $this->playerId;
    }
}
