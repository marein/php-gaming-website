<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Projection;

use Gaming\Common\EventStore\DomainEvent;
use Gaming\Common\EventStore\NoCommit;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\ConnectFour\Domain\Game\Event\GameAborted;
use Gaming\ConnectFour\Domain\Game\Event\GameDrawn;
use Gaming\ConnectFour\Domain\Game\Event\GameResigned;
use Gaming\ConnectFour\Domain\Game\Event\GameTimedOut;
use Gaming\ConnectFour\Domain\Game\Event\GameWon;
use Gaming\ConnectFour\Domain\Game\Event\PlayerMoved;
use Gaming\ConnectFour\Port\Adapter\Persistence\Repository\PredisTimerStore;

final class TimerProjection implements StoredEventSubscriber
{
    /**
     * @var array<string, int>
     */
    private array $additions = [];

    /**
     * @var string[]
     */
    private array $removals = [];

    public function __construct(
        private readonly PredisTimerStore $predisTimerStore
    ) {
    }

    public function handle(DomainEvent $domainEvent): void
    {
        $content = $domainEvent->content;

        match ($content::class) {
            PlayerMoved::class => $this->additions[$domainEvent->streamId] = (int)$content->nextPlayerTurnEndsAt,
            GameAborted::class,
            GameDrawn::class,
            GameResigned::class,
            GameTimedOut::class,
            GameWon::class => $this->removals[] = $domainEvent->streamId,
            default => true
        };
    }

    public function commit(): void
    {
        $this->predisTimerStore->add(array_splice($this->additions, 0));
        $this->predisTimerStore->remove(array_splice($this->removals, 0));
    }
}
