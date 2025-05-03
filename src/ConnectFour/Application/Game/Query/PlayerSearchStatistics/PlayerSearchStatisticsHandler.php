<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\PlayerSearchStatistics;

use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GamesByPlayerStore;
use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\State;

final class PlayerSearchStatisticsHandler
{
    public function __construct(
        private readonly GamesByPlayerStore $gamesByPlayerStore
    ) {
    }

    public function __invoke(PlayerSearchStatisticsQuery $query): PlayerSearchStatisticsResponse
    {
        return $query->playerId === null
            ? new PlayerSearchStatisticsResponse(
                array_combine(
                    array_map(static fn(State $state): string => $state->value, State::cases()),
                    array_fill(0, count(State::cases()), 0)
                )
            )
            : $this->gamesByPlayerStore->searchStatistics($query->playerId);
    }
}
