<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query;

use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GamesByPlayer;
use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GamesByPlayerStore;

final class GamesByPlayerHandler
{
    /**
     * @var GamesByPlayerStore
     */
    private GamesByPlayerStore $gamesByPlayerFinder;

    /**
     * GamesByPlayerHandler constructor.
     *
     * @param GamesByPlayerStore $gamesByPlayerFinder
     */
    public function __construct(GamesByPlayerStore $gamesByPlayerFinder)
    {
        $this->gamesByPlayerFinder = $gamesByPlayerFinder;
    }

    /**
     * @param GamesByPlayerQuery $query
     *
     * @return GamesByPlayer
     */
    public function __invoke(GamesByPlayerQuery $query): GamesByPlayer
    {
        return $this->gamesByPlayerFinder->all(
            $query->playerId()
        );
    }
}
