<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\PlayerSearchStatistics;

final class PlayerSearchStatisticsResponse
{
    /**
     * @param array<string, int> $states
     */
    public function __construct(
        public readonly array $states
    ) {
    }
}
