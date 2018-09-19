<?php
declare(strict_types=1);

namespace Gambling\ConnectFour\Application\Game\Query;

final class GamesByPlayerQuery
{
    /**
     * @var string
     */
    private $playerId;

    /**
     * GameQuery constructor.
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
