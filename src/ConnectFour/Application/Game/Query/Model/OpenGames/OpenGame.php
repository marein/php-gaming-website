<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\Model\OpenGames;

final class OpenGame
{
    /**
     * @var string
     */
    private string $gameId;

    /**
     * @var string
     */
    private string $playerId;

    /**
     * OpenGame constructor.
     *
     * @param string $gameId
     * @param string $playerId
     */
    public function __construct(string $gameId, string $playerId)
    {
        $this->gameId = $gameId;
        $this->playerId = $playerId;
    }

    /**
     * @return string
     */
    public function gameId(): string
    {
        return $this->gameId;
    }

    /**
     * @return string
     */
    public function playerId(): string
    {
        return $this->playerId;
    }
}
