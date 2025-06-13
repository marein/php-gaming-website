<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Event;

use Gaming\Common\Domain\DomainEvent;

final class GameTimedOut implements DomainEvent
{
    public function __construct(
        public readonly string $gameId,
        public readonly string $timedOutPlayerId,
        public readonly string $opponentPlayerId
    ) {
    }

    public function aggregateId(): string
    {
        return $this->gameId;
    }
}
