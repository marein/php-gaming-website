<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game;

use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\Exception\PlayerHasInvalidStoneException;
use Gaming\ConnectFour\Domain\Game\Timer\Timer;

final class Player
{
    private readonly Timer $timer;

    /**
     * @throws PlayerHasInvalidStoneException
     */
    public function __construct(
        private readonly string $playerId,
        private readonly Stone $stone,
        ?Timer $timer = null
    ) {
        $this->timer = $timer ?? Timer::set(60 * 5);

        $this->guardPlayerHasCorrectStone($stone);
    }

    /**
     * @throws PlayerHasInvalidStoneException
     */
    private function guardPlayerHasCorrectStone(Stone $stone): void
    {
        if ($stone === Stone::None) {
            throw new PlayerHasInvalidStoneException('Stone should be Stone::Red or Stone::Yellow.');
        }
    }

    public function id(): string
    {
        return $this->playerId;
    }

    public function stone(): Stone
    {
        return $this->stone;
    }
}
