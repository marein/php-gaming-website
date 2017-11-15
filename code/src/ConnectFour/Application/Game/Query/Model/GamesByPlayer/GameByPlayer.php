<?php

namespace Gambling\ConnectFour\Application\Game\Query\Model\GamesByPlayer;

final class GameByPlayer
{
    /**
     * @var string
     */
    private $gameId;

    /**
     * GameByPlayer constructor.
     *
     * @param string $gameId
     */
    public function __construct(string $gameId)
    {
        $this->gameId = $gameId;
    }

    /**
     * @return string
     */
    public function gameId(): string
    {
        return $this->gameId;
    }
}
