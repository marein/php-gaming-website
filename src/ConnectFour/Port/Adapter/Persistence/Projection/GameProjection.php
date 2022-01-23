<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Projection;

use Gaming\Common\EventStore\StoredEvent;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\Game;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\GameStore;
use Gaming\ConnectFour\Domain\Game\Event\GameOpened;
use Gaming\ConnectFour\Port\Adapter\Persistence\Repository\InMemoryCacheGameStore;

final class GameProjection implements StoredEventSubscriber
{
    private readonly GameStore $gameStore;

    public function __construct(GameStore $gameStore)
    {
        $this->gameStore = new InMemoryCacheGameStore(
            $gameStore,
            1000
        );
    }

    public function handle(StoredEvent $storedEvent): void
    {
        $domainEvent = $storedEvent->domainEvent();

        $game = match ($domainEvent::class) {
            GameOpened::class => new Game(),
            default => $this->gameStore->find($domainEvent->aggregateId())
        };

        $game->apply($domainEvent);

        $this->gameStore->save($game);
    }

    public function isSubscribedTo(StoredEvent $storedEvent): bool
    {
        return true;
    }
}
