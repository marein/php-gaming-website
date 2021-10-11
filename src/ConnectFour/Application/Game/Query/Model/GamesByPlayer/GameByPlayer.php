<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer;

final class GameByPlayer
{
    private string $gameId;

    public function __construct(string $gameId)
    {
        $this->gameId = $gameId;
    }

    public function gameId(): string
    {
        return $this->gameId;
    }
}
