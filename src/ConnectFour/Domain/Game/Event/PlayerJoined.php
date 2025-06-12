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
        public readonly string $redPlayerId,
        public readonly int $redPlayerRemainingMs,
        public readonly ?int $redPlayerTurnEndsAt,
        public readonly string $yellowPlayerId,
        public readonly int $yellowPlayerRemainingMs
    ) {
        $this->gameId = $gameId->toString();
    }

    public function aggregateId(): string
    {
        return $this->gameId;
    }
}
