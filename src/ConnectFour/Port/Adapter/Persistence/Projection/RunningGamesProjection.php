<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Projection;

use Gaming\Common\EventStore\DomainEvent;
use Gaming\Common\EventStore\NoCommit;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\ConnectFour\Application\Game\Query\Model\RunningGames\RunningGameStore;
use Gaming\ConnectFour\Domain\Game\Event\GameAborted;
use Gaming\ConnectFour\Domain\Game\Event\GameDrawn;
use Gaming\ConnectFour\Domain\Game\Event\GameResigned;
use Gaming\ConnectFour\Domain\Game\Event\GameTimedOut;
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
        $content = $domainEvent->content;

        match ($content::class) {
            PlayerJoined::class => $this->runningGameStore->add($domainEvent->streamId),
            GameAborted::class,
            GameDrawn::class,
            GameResigned::class,
            GameTimedOut::class,
            GameWon::class => $this->runningGameStore->remove($domainEvent->streamId),
            default => true
        };
    }
}
