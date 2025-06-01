<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game;

use DateTimeImmutable;
use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\Exception\PlayerHasInvalidStoneException;
use Gaming\ConnectFour\Domain\Game\Timer\Timer;

final class Player
{
    /**
     * @throws PlayerHasInvalidStoneException
     */
    public function __construct(
        private readonly string $playerId,
        private readonly Stone $stone,
        private readonly Timer $timer
    ) {
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

    public function startTurn(DateTimeImmutable $now = new DateTimeImmutable()): self
    {
        return new self(
            $this->playerId,
            $this->stone,
            $this->timer->start($now)
        );
    }

    public function endTurn(DateTimeImmutable $now = new DateTimeImmutable()): self
    {
        return new self(
            $this->playerId,
            $this->stone,
            $this->timer->stop($now)
        );
    }

    public function remainingMs(): int
    {
        return $this->timer->remainingMs;
    }
}
