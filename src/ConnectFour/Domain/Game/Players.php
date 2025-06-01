<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game;

use DateTimeImmutable;
use Gaming\ConnectFour\Domain\Game\Exception\PlayerNotOwnerException;
use Gaming\ConnectFour\Domain\Game\Exception\PlayersNotUniqueException;

final class Players
{
    private Player $currentPlayer;

    private Player $nextPlayer;

    /**
     * @throws PlayersNotUniqueException
     */
    public function __construct(Player $currentPlayer, Player $nextPlayer)
    {
        if ($currentPlayer->id() === $nextPlayer->id()) {
            throw new PlayersNotUniqueException();
        }

        $this->currentPlayer = $currentPlayer;
        $this->nextPlayer = $nextPlayer;
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
