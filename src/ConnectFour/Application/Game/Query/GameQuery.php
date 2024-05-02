<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query;

use Gaming\Common\Bus\Request;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\Game;

/**
 * @implements Request<Game>
 */
final class GameQuery implements Request
{
    public function __construct(
        private readonly string $gameId
    ) {
    }

    public function gameId(): string
    {
        return $this->gameId;
    }
}
