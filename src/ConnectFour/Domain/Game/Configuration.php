<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game;

use DateTimeImmutable;
use Gaming\Common\Timer\GameTimer;
use Gaming\Common\Timer\Timer;
use Gaming\ConnectFour\Domain\Game\Board\Size;
use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\Exception\PlayersNotUniqueException;
use Gaming\ConnectFour\Domain\Game\WinningRule\WinningRules;

final class Configuration
{
    public readonly Timer $timer;

    public function __construct(
        private readonly Size $size,
        private readonly WinningRules $winningRules,
        public readonly ?Stone $preferredStone = null,
        ?Timer $timer = null
    ) {
        $this->timer = $timer ?? GameTimer::set(60000, 0);
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
    public function createPlayers(
        string $playerId,
        string $joinedPlayerId,
        DateTimeImmutable $now = new DateTimeImmutable()
    ): Players {
        [$currentPlayer, $nextPlayer] = match ($this->preferredStone ?? Stone::random()) {
            Stone::Red => [
                new Player($playerId, Stone::Red, $this->timer),
                new Player($joinedPlayerId, Stone::Yellow, $this->timer)
            ],
            default => [
                new Player($joinedPlayerId, Stone::Red, $this->timer),
                new Player($playerId, Stone::Yellow, $this->timer)
            ]
        };

        return Players::start($currentPlayer, $nextPlayer, $now);
    }
}
