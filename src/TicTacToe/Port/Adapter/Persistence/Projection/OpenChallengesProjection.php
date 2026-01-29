<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Port\Adapter\Persistence\Projection;

use Gaming\Common\EventStore\DomainEvent;
use Gaming\Common\EventStore\NoCommit;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\TicTacToe\Application\Model\OpenChallenges\OpenChallenge;
use Gaming\TicTacToe\Application\Model\OpenChallenges\OpenChallengesStore;
use Gaming\TicTacToe\Domain\Challenge\Event\ChallengeAccepted;
use Gaming\TicTacToe\Domain\Challenge\Event\ChallengeOpened;
use Gaming\TicTacToe\Domain\Challenge\Event\ChallengeWithdrawn;

final class OpenChallengesProjection implements StoredEventSubscriber
{
    use NoCommit;

    public function __construct(
        private readonly OpenChallengesStore $openChallengesStore
    ) {
    }

    public function handle(DomainEvent $domainEvent): void
    {
        $content = $domainEvent->content;

        match ($content::class) {
            ChallengeOpened::class => $this->openChallengesStore->save(
                new OpenChallenge(
                    $content->challengeId,
                    $content->size,
                    $content->preferredToken,
                    $content->timer,
                    $content->playerId
                )
            ),
            ChallengeWithdrawn::class,
            ChallengeAccepted::class => $this->openChallengesStore->remove($content->challengeId),
            default => true
        };
    }
}
