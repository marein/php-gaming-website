<?php

namespace Gambling\ConnectFour\Application\Game\Query\Model\GamesByPlayer;

final class GamesByPlayer
{
    /**
     * @var GameByPlayer[]
     */
    private $games;

    /**
     * GamesByPlayer constructor.
     *
     * @param GameByPlayer[] $games
     */
    public function __construct(array $games)
    {
        $this->games = $games;
    }

    /**
     * @return GameByPlayer[]
     */
    public function games(): array
    {
        return $this->games;
    }
}
