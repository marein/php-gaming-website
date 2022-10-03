<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game;

use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\Exception\PlayerHasInvalidStoneException;

final class Player
{
    private string $playerId;

    private Stone $stone;

    /**
     * @throws PlayerHasInvalidStoneException
     */
    public function __construct(string $playerId, Stone $stone)
    {
        $this->playerId = $playerId;
        $this->stone = $stone;

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
