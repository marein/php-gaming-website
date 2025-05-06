<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query;

use Gaming\Common\Bus\Request;
use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GamesByPlayer;
use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\State;

/**
 * @implements Request<GamesByPlayer>
 */
final class GamesByPlayerQuery implements Request
{
    public function __construct(
        public readonly string $playerId,
        public readonly State $state,
        public readonly int $page,
        public readonly int $limit
    ) {
    }
}
