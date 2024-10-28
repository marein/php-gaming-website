<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Event;

use Gaming\Common\Domain\DomainEvent;
use Gaming\ConnectFour\Domain\Game\Board\Point;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\Player;

final class GameWon implements DomainEvent
{
    private string $gameId;

    private string $winnerPlayerId;

    /**
     * @var Point[]
     */
    private array $winningSequence;

    /**
     * @param Point[] $winningSequence
     */
    public function __construct(GameId $gameId, Player $winnerPlayer, array $winningSequence)
    {
        $this->gameId = $gameId->toString();
        $this->winnerPlayerId = $winnerPlayer->id();
        $this->winningSequence = $winningSequence;
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
     * @return Point[]
     */
    public function winningSequence(): array
    {
        return $this->winningSequence;
    }
}
