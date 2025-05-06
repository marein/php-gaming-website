<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer;

use Gaming\ConnectFour\Application\Game\Query\Model\Game\Game;

final class GamesByPlayer
{
    /**
     * @param Game[] $games
     */
    public function __construct(
        public readonly int $total,
        public readonly array $games
    ) {
    }
}
