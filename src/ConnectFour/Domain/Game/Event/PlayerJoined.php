<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Event;

use Gaming\Common\Domain\DomainEvent;
use Gaming\ConnectFour\Domain\Game\GameId;

final class PlayerJoined implements DomainEvent
{
    private string $gameId;

    public function __construct(
        GameId $gameId,
        private readonly string $joinedPlayerId,
        private readonly string $opponentPlayerId,
        public readonly string $redPlayerId,
        public readonly string $yellowPlayerId
    ) {
        $this->gameId = $gameId->toString();
    }

    public function aggregateId(): string
    {
        return $this->gameId;
    }

    public function joinedPlayerId(): string
    {
        return $this->joinedPlayerId;
    }

    public function opponentPlayerId(): string
    {
        return $this->opponentPlayerId;
    }
}
