<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Port\Adapter\Persistence\Repository;

use Closure;
use Gaming\Common\Domain\Exception\ConcurrencyException;
use Gaming\Common\EventStore\DomainEvents;
use Gaming\Common\EventStore\Exception\DuplicateVersionInStreamException;
use Gaming\Common\EventStore\Integration\Doctrine\DoctrineEventStore;
use Gaming\TicTacToe\Domain\Challenge\Challenge;
use Gaming\TicTacToe\Domain\Challenge\ChallengeId;
use Gaming\TicTacToe\Domain\Challenge\Challenges;
use Gaming\TicTacToe\Domain\Challenge\Event\ChallengeOpened;
use Gaming\TicTacToe\Domain\Challenge\Exception\ChallengeNotFoundException;

final class EventStoreChallenges implements Challenges
{
    public function __construct(
        private readonly DoctrineEventStore $eventStore
    ) {
    }

    public function nextIdentity(): ChallengeId
    {
        return ChallengeId::generate();
    }

    public function add(Challenge $challenge): void
    {
        $this->appendDomainEvents($challenge);
    }

    public function update(ChallengeId $challengeId, Closure $operation): void
    {
        $domainEvents = $this->eventStore->byStreamId($challengeId->toString());
        if (count($domainEvents) === 0 || !$domainEvents[0]->content instanceof ChallengeOpened) {
            throw new ChallengeNotFoundException();
        }

        $challenge = Challenge::fromHistory(
            $challengeId,
            new DomainEvents($challengeId->toString(), count($domainEvents), $domainEvents)
        );

        $operation($challenge);

        $this->appendDomainEvents($challenge);
    }

    /**
     * @throws ConcurrencyException
     */
    private function appendDomainEvents(Challenge $challenge): void
    {
        try {
            $this->eventStore->append(...$challenge->flushDomainEvents());
        } catch (DuplicateVersionInStreamException) {
            throw new ConcurrencyException();
        }
    }
}
