<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query;

use Gaming\Common\Bus\Request;
use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GamesByPlayer;

/**
 * @implements Request<GamesByPlayer>
 */
final class GamesByPlayerQuery implements Request
{
    public function __construct(
        private readonly string $playerId
    ) {
    }

    public function playerId(): string
    {
        return $this->playerId;
    }
}
