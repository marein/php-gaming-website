<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Domain\Challenge\Event;

use Gaming\Common\Domain\DomainEvent;

final class ChallengeOpened implements DomainEvent
{
    public function __construct(
        public readonly string $challengeId,
        public readonly string $challengerId,
    ) {
    }

    public function aggregateId(): string
    {
        return $this->challengeId;
    }
}
