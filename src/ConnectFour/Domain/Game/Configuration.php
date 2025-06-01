<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game;

use Gaming\ConnectFour\Domain\Game\Board\Size;
use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\Exception\PlayersNotUniqueException;
use Gaming\ConnectFour\Domain\Game\Timer\Timer;
use Gaming\ConnectFour\Domain\Game\WinningRule\WinningRules;

final class Configuration
{
    public function __construct(
        private readonly Size $size,
        private readonly WinningRules $winningRules,
        public readonly ?Stone $preferredStone = null
    ) {
    }

    public static function common(): Configuration
    {
        return new self(
            new Size(7, 6),
            WinningRules::standard(),
            Stone::Red // Should be null, but is Red for keeping unit tests green.
        );
    }

    public function size(): Size
    {
        return $this->size;
    }

    public function winningRules(): WinningRules
    {
        return $this->winningRules;
    }

    /**
     * @throws PlayersNotUniqueException
     */
    public function createPlayers(string $playerId, string $joinedPlayerId): Players
    {
        $timer = Timer::set(60 * 5);

        $players = match ($this->preferredStone ?? Stone::random()) {
            Stone::Red => [
                new Player($playerId, Stone::Red, $timer),
                new Player($joinedPlayerId, Stone::Yellow, $timer)
            ],
            default => [
                new Player($joinedPlayerId, Stone::Red, $timer),
                new Player($playerId, Stone::Yellow, $timer)
            ]
        };

        return new Players(...$players);
    }
}
