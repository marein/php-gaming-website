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
    private array $players;

    private int $currentPlayerPosition;

    /**
     * @param Player[] $players
     */
    private function __construct(array $players, int $currentPlayerPosition)
    {
        $this->players = $players;
        $this->currentPlayerPosition = $currentPlayerPosition;
    }

    public static function beginWith(Player $player): PlayerPool
    {
        return new self(
            [$player],
            0
        );
    }

    /**
     * This function resets the current player position.
     *
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
     * This function resets the current player position.
     *
     * @throws PlayerNotJoinedException
     */
    public function leave(Player $player): PlayerPool
    {
        $players = array_filter(
            $this->players,
            static fn(Player $current): bool => $player->id() !== $current->id()
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
     * @throws PlayerPoolIsEmptyException
     */
    public function current(): Player
    {
        $this->throwExceptionIfPoolIsEmpty();

        return $this->players[$this->currentPlayerPosition];
    }

    /**
     * @return Player[]
     */
    public function players(): array
    {
        return $this->players;
    }

    public function isEmpty(): bool
    {
        return count($this->players) === 0;
    }

    /**
     * @throws PlayerPoolIsEmptyException
     */
    private function throwExceptionIfPoolIsEmpty(): void
    {
        if ($this->isEmpty()) {
            throw new PlayerPoolIsEmptyException();
        }
    }

    /**
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
