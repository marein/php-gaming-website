<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Application\Model;

use Gaming\TicTacToe\Domain\Challenge\Event\ChallengeAccepted;
use Gaming\TicTacToe\Domain\Challenge\Event\ChallengeOpened;
use Gaming\TicTacToe\Domain\Challenge\Event\ChallengeWithdrawn;

final class Challenge
{
    public const STATUS_OPEN = 'open';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_WITHDRAWN = 'withdrawn';

    public function __construct(
        public readonly string $challengeId,
        public private(set) string $challengerId = '',
        public private(set) string $status = self::STATUS_OPEN,
        public private(set) string $acceptorId = ''
    ) {
    }

    public function apply(object $domainEvent): self
    {
        match (true) {
            $domainEvent instanceof ChallengeOpened => $this->onChallengeOpened($domainEvent),
            $domainEvent instanceof ChallengeAccepted => $this->onChallengeAccepted($domainEvent),
            $domainEvent instanceof ChallengeWithdrawn => $this->onChallengeWithdrawn($domainEvent),
            default => null,
        };

        return $this;
    }

    private function onChallengeOpened(ChallengeOpened $event): void
    {
        $this->status = self::STATUS_OPEN;
        $this->challengerId = $event->playerId;
    }

    private function onChallengeAccepted(ChallengeAccepted $event): void
    {
        $this->status = self::STATUS_ACCEPTED;
        $this->acceptorId = $event->acceptorId;
    }

    private function onChallengeWithdrawn(ChallengeWithdrawn $event): void
    {
        $this->status = self::STATUS_WITHDRAWN;
    }
}
