<?php
declare(strict_types=1);

namespace Gaming\Memory\Domain\Model\Game;

use Gaming\Memory\Domain\Model\Game\Exception\PlayerAlreadyJoinedException;

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
            $this->currentPlayerPosition
        );
    }

    /**
     * Returns the players in switched position.
     *
     * @return PlayerPool
     */
    public function switch(): PlayerPool
    {
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
     */
    public function current(): Player
    {
        return $this->players[$this->currentPlayerPosition];
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
