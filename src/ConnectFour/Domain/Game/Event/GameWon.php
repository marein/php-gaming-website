<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Event;

use Gaming\Common\Domain\DomainEvent;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\WinningRule\WinningSequence;

final class GameWon implements DomainEvent
{
    private string $gameId;

    /**
     * @param WinningSequence[] $winningSequences
     */
    public function __construct(
        GameId $gameId,
        public readonly string $winnerId,
        public readonly string $loserId,
        public readonly array $winningSequences
    ) {
        $this->gameId = $gameId->toString();
    }

    public function aggregateId(): string
    {
        return $this->gameId;
    }

    public function winnerPlayerId(): string
    {
        return $this->winnerId;
    }

    /**
     * @return WinningSequence[]
     */
    public function winningSequences(): array
    {
        return $this->winningSequences;
    }
}
