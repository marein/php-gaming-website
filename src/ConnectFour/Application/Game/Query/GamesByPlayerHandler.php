<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query;

use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GamesByPlayer;
use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GamesByPlayerStore;

final class GamesByPlayerHandler
{
    private GamesByPlayerStore $gamesByPlayerFinder;

    public function __construct(GamesByPlayerStore $gamesByPlayerFinder)
    {
        $this->gamesByPlayerFinder = $gamesByPlayerFinder;
    }

    public function __invoke(GamesByPlayerQuery $query): GamesByPlayer
    {
        return $this->gamesByPlayerFinder->search(
            $query->playerId,
            $query->state,
            $query->page,
            $query->limit
        );
    }
}
