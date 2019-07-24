<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game;

use Gaming\ConnectFour\Domain\Game\Exception\PlayerNotOwnerException;
use Gaming\ConnectFour\Domain\Game\Exception\PlayersNotUniqueException;

final class Players
{
    /**
     * @var Player
     */
    private $currentPlayer;

    /**
     * @var Player
     */
    private $nextPlayer;

    /**
     * Players constructor.
     *
     * @param Player $currentPlayer
     * @param Player $nextPlayer
     *
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

    /**
     * Returns the players in switched position.
     *
     * @return Players
     */
    public function switch(): Players
    {
        return new self(
            $this->nextPlayer,
            $this->currentPlayer
        );
    }

    /**
     * Returns the current player.
     *
     * @return Player
     */
    public function current(): Player
    {
        return $this->currentPlayer;
    }

    /**
     * Returns the player with the given player id.
     *
     * @param string $playerId
     *
     * @return Player
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
     * Returns the opponent of the player with the given player id.
     *
     * @param string $playerId
     *
     * @return Player
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
