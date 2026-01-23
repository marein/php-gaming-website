<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Domain\Challenge;

use Gaming\Common\EventStore\CollectsDomainEvents;
use Gaming\Common\EventStore\DomainEvent;
use Gaming\Common\EventStore\DomainEvents;
use Gaming\TicTacToe\Domain\Challenge\Event\ChallengeAccepted;
use Gaming\TicTacToe\Domain\Challenge\Event\ChallengeOpened;
use Gaming\TicTacToe\Domain\Challenge\Event\ChallengeWithdrawn;
use Gaming\TicTacToe\Domain\Challenge\Exception\CannotAcceptOwnChallengeException;
use Gaming\TicTacToe\Domain\Challenge\Exception\CannotWithdrawException;
use Gaming\TicTacToe\Domain\Challenge\Exception\NotOpenException;

final class Challenge implements CollectsDomainEvents
{
    private const int STATE_OPEN = 1;
    private const int STATE_CLOSED = 2;
    private int $state = self::STATE_OPEN;

    private string $challengerId = '';

    private function __construct(
        public readonly ChallengeId $challengeId,
        private readonly DomainEvents $domainEvents
    ) {
    }

    public static function open(ChallengeId $challengeId, string $playerId): self
    {
        $challenge = new self($challengeId, new DomainEvents($challengeId->toString()));
        $challenge->record(
            new ChallengeOpened(
                $challengeId->toString(),
                3,
                3,
                $playerId
            )
        );

        return $challenge;
    }

    public static function fromHistory(ChallengeId $challengeId, DomainEvents $domainEvents): self
    {
        return array_reduce(
            $domainEvents->flush(),
            static fn(Challenge $challenge, DomainEvent $event): Challenge => $challenge->apply($event->content),
            new self($challengeId, new DomainEvents((string)$challengeId, $domainEvents->streamVersion()))
        );
    }

    /**
     * @throws NotOpenException
     * @throws CannotWithdrawException
     */
    public function withdraw(string $playerId): void
    {
        if ($this->state !== self::STATE_OPEN) {
            throw new NotOpenException();
        }

        if ($this->challengerId !== $playerId) {
            throw new CannotWithdrawException();
        }

        $this->record(
            new ChallengeWithdrawn(
                $this->challengeId->toString(),
                $this->challengerId
            )
        );
    }

    /**
     * @throws NotOpenException
     * @throws CannotAcceptOwnChallengeException
     */
    public function accept(string $acceptorId): void
    {
        if ($this->state !== self::STATE_OPEN) {
            throw new NotOpenException();
        }

        if ($this->challengerId === $acceptorId) {
            throw new CannotAcceptOwnChallengeException();
        }

        $this->record(
            new ChallengeAccepted(
                $this->challengeId->toString(),
                $this->challengerId,
                $acceptorId
            )
        );
    }

    /**
     * @return DomainEvent[]
     */
    public function flushDomainEvents(): array
    {
        return $this->domainEvents->flush();
    }

    private function record(object $event): void
    {
        $this->domainEvents->append($event);
        $this->apply($event);
    }

    private function apply(object $event): self
    {
        match ($event::class) {
            ChallengeOpened::class => $this->challengerId = $event->playerId,
            ChallengeAccepted::class,
            ChallengeWithdrawn::class => $this->state = self::STATE_CLOSED,
            default => null
        };

        return $this;
    }
}
