<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\PlayerSearchStatistics;

use Gaming\Common\Bus\Request;

/**
 * @implements Request<PlayerSearchStatisticsResponse>
 */
final class PlayerSearchStatisticsQuery implements Request
{
    public function __construct(
        public readonly ?string $playerId
    ) {
    }
}
