<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game;

use Gaming\ConnectFour\Domain\Game\Board\Size;
use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\Exception\PlayersNotUniqueException;
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
        $players = match ($this->preferredStone ?? Stone::random()) {
            Stone::Red => [
                new Player($playerId, Stone::Red),
                new Player($joinedPlayerId, Stone::Yellow)
            ],
            default => [
                new Player($joinedPlayerId, Stone::Red),
                new Player($playerId, Stone::Yellow)
            ]
        };

        return new Players(...$players);
    }
}
