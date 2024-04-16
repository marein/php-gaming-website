<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Projection;

use Gaming\Common\Domain\DomainEvent;
use Gaming\Common\EventStore\NoCommit;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\ConnectFour\Application\Game\Query\Model\RunningGames\RunningGameStore;
use Gaming\ConnectFour\Domain\Game\Event\GameAborted;
use Gaming\ConnectFour\Domain\Game\Event\GameDrawn;
use Gaming\ConnectFour\Domain\Game\Event\GameResigned;
use Gaming\ConnectFour\Domain\Game\Event\GameWon;
use Gaming\ConnectFour\Domain\Game\Event\PlayerJoined;

final class RunningGamesProjection implements StoredEventSubscriber
{
    use NoCommit;

    public function __construct(
        private readonly RunningGameStore $runningGameStore
    ) {
    }

    public function handle(DomainEvent $domainEvent): void
    {
        match ($domainEvent::class) {
            PlayerJoined::class => $this->addGame($domainEvent->aggregateId()),
            GameAborted::class,
            GameDrawn::class,
            GameResigned::class,
            GameWon::class => $this->removeGame($domainEvent->aggregateId()),
            default => true
        };
    }

    private function addGame(string $gameId): void
    {
        $this->runningGameStore->add($gameId);
    }

    private function removeGame(string $gameId): void
    {
        $this->runningGameStore->remove($gameId);
    }
}
