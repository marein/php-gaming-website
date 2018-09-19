<?php
declare(strict_types=1);

namespace Gambling\ConnectFour\Application\Game\Query;

final class GameQuery
{
    /**
     * @var string
     */
    private $gameId;

    /**
     * GameQuery constructor.
     *
     * @param string $gameId
     */
    public function __construct(string $gameId)
    {
        $this->gameId = $gameId;
    }

    public function gameId(): string
    {
        return $this->gameId;
    }
}
