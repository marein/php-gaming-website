<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Event;

use Gaming\Common\Domain\DomainEvent;
use Gaming\ConnectFour\Domain\Game\GameId;

final class GameAborted implements DomainEvent
{
    private string $gameId;

    public function __construct(
        GameId $gameId,
        private readonly string $abortedPlayerId,
        private readonly string $opponentPlayerId = ''
    ) {
        $this->gameId = $gameId->toString();
    }

    public function aggregateId(): string
    {
        return $this->gameId;
    }

    public function abortedPlayerId(): string
    {
        return $this->abortedPlayerId;
    }

    public function opponentPlayerId(): string
    {
        return $this->opponentPlayerId;
    }
}
