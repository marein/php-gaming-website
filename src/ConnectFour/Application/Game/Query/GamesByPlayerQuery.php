<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query;

final class GamesByPlayerQuery
{
    /**
     * @var string
     */
    private string $playerId;

    /**
     * GamesByPlayerQuery constructor.
     *
     * @param string $playerId
     */
    public function __construct(string $playerId)
    {
        $this->playerId = $playerId;
    }

    public function playerId(): string
    {
        return $this->playerId;
    }
}
