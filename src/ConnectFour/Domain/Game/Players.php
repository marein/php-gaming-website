<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game;

use DateTimeImmutable;
use Gaming\ConnectFour\Domain\Game\Exception\PlayerNotOwnerException;
use Gaming\ConnectFour\Domain\Game\Exception\PlayersNotUniqueException;

final class Players
{
    /**
     * @throws PlayersNotUniqueException
     */
    private function __construct(
        private readonly Player $currentPlayer,
        private readonly Player $nextPlayer
    ) {
        if ($currentPlayer->id() === $nextPlayer->id()) {
            throw new PlayersNotUniqueException();
        }
    }

    public static function start(
        Player $currentPlayer,
        Player $nextPlayer,
        DateTimeImmutable $now = new DateTimeImmutable()
    ): self {
        return new self(
            $currentPlayer->startTurn($now),
            $nextPlayer
        );
    }

    public function switch(DateTimeImmutable $now = new DateTimeImmutable()): Players
    {
        return new self(
            $this->nextPlayer->startTurn($now),
            $this->currentPlayer->endTurn($now)
        );
    }

    public function current(): Player
    {
        return $this->currentPlayer;
    }

    public function next(): Player
    {
        return $this->nextPlayer;
    }

    /**
     * @throws PlayerNotOwnerException
     */
    public function get(string $playerId): Player
    {
        if ($this->currentPlayer->id() === $playerId) {
            return $this->currentPlayer;
        }

        if ($this->nextPlayer->id() === $playerId) {
            return $this->nextPlayer;
        }

        throw new PlayerNotOwnerException();
    }

    /**
     * @throws PlayerNotOwnerException
     */
    public function opponentOf(string $playerId): Player
    {
        if ($this->currentPlayer->id() === $playerId) {
            return $this->nextPlayer;
        }

        if ($this->nextPlayer->id() === $playerId) {
            return $this->currentPlayer;
        }

        throw new PlayerNotOwnerException();
    }
}
