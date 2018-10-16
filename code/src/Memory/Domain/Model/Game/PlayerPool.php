<?php
declare(strict_types=1);

namespace Gaming\Memory\Domain\Model\Game;

use Gaming\Memory\Domain\Model\Game\Exception\PlayerAlreadyJoinedException;
use Gaming\Memory\Domain\Model\Game\Exception\PlayerNotJoinedException;
use Gaming\Memory\Domain\Model\Game\Exception\PlayerPoolIsEmptyException;

final class PlayerPool
{
    /**
     * @var Player[]
     */
    private $players;

    /**
     * @var int
     */
    private $currentPlayerPosition;

    /**
     * PlayerPool constructor.
     *
     * @param Player[] $players
     * @param int      $currentPlayerPosition
     */
    private function __construct(array $players, int $currentPlayerPosition)
    {
        $this->players = $players;
        $this->currentPlayerPosition = $currentPlayerPosition;
    }

    /**
     * Create a pool of players.
     *
     * @param Player $player
     *
     * @return PlayerPool
     */
    public static function beginWith(Player $player): PlayerPool
    {
        return new self(
            [$player],
            0
        );
    }

    /**
     * A player joins the pool.
     * This function resets the current player position.
     *
     * @param Player $player
     *
     * @return PlayerPool
     * @throws PlayerAlreadyJoinedException
     */
    public function join(Player $player): PlayerPool
    {
        $this->throwExceptionIfPlayerAlreadyJoined($player);

        $players = $this->players;
        $players[] = $player;

        return new self(
            $players,
            0
        );
    }

    /**
     * A player leaves the pool.
     * This function resets the current player position.
     *
     * @param Player $player
     *
     * @return PlayerPool
     * @throws PlayerNotJoinedException
     */
    public function leave(Player $player): PlayerPool
    {
        $players = array_filter(
            $this->players,
            function (Player $current) use ($player) {
                return $player->id() !== $current->id();
            }
        );

        if (count($players) === count($this->players)) {
            throw new PlayerNotJoinedException();
        }

        return new self(
            array_values($players),
            0
        );
    }

    /**
     * Returns the players in switched position.
     *
     * @return PlayerPool
     * @throws PlayerPoolIsEmptyException
     */
    public function switch(): PlayerPool
    {
        $this->throwExceptionIfPoolIsEmpty();

        $nextPlayerPosition = $this->currentPlayerPosition + 1;

        return new self(
            $this->players,
            $nextPlayerPosition > count($this->players) - 1 ? 0 : $nextPlayerPosition
        );
    }

    /**
     * Returns the current player.
     *
     * @return Player
     * @throws PlayerPoolIsEmptyException
     */
    public function current(): Player
    {
        $this->throwExceptionIfPoolIsEmpty();

        return $this->players[$this->currentPlayerPosition];
    }

    /**
     * Returns true if the pool is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return count($this->players) === 0;
    }

    /**
     * Throw an exception if the pool is empty.
     *
     * @throws PlayerPoolIsEmptyException
     */
    private function throwExceptionIfPoolIsEmpty(): void
    {
        if ($this->isEmpty()) {
            throw new PlayerPoolIsEmptyException();
        }
    }

    /**
     * Throw an exception if player already joined the pool.
     *
     * @param Player $player
     *
     * @throws PlayerAlreadyJoinedException
     */
    private function throwExceptionIfPlayerAlreadyJoined(Player $player): void
    {
        foreach ($this->players as $currentPlayer) {
            if ($currentPlayer->id() === $player->id()) {
                throw new PlayerAlreadyJoinedException();
            }
        }
    }
}
