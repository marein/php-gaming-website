<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Event;

use Gaming\Common\Domain\DomainEvent;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\Player;
use Gaming\ConnectFour\Domain\Game\WinningRule\WinningSequence;

final class GameWon implements DomainEvent
{
    private string $gameId;

    private string $winnerPlayerId;

    /**
     * @var WinningSequence[]
     */
    private array $winningSequences;

    /**
     * @param WinningSequence[] $winningSequences
     */
    public function __construct(GameId $gameId, Player $winnerPlayer, array $winningSequences)
    {
        $this->gameId = $gameId->toString();
        $this->winnerPlayerId = $winnerPlayer->id();
        $this->winningSequences = $winningSequences;
    }

    public function aggregateId(): string
    {
        return $this->gameId;
    }

    public function winnerPlayerId(): string
    {
        return $this->winnerPlayerId;
    }

    /**
     * @return WinningSequence[]
     */
    public function winningSequences(): array
    {
        return $this->winningSequences;
    }
}
