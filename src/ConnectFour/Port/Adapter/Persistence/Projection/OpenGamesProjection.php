<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Projection;

use Gaming\Common\EventStore\StoredEvent;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\ConnectFour\Application\Game\Query\Model\OpenGames\OpenGame;
use Gaming\ConnectFour\Application\Game\Query\Model\OpenGames\OpenGameStore;
use Gaming\ConnectFour\Domain\Game\Event\GameAborted;
use Gaming\ConnectFour\Domain\Game\Event\GameOpened;
use Gaming\ConnectFour\Domain\Game\Event\PlayerJoined;

final class OpenGamesProjection implements StoredEventSubscriber
{
    public function __construct(
        private readonly OpenGameStore $openGameStore
    ) {
    }

    public function handle(StoredEvent $storedEvent): void
    {
        $domainEvent = $storedEvent->domainEvent();

        match ($domainEvent::class) {
            GameOpened::class => $this->saveGame($domainEvent->aggregateId(), $domainEvent->playerId()),
            GameAborted::class,
            PlayerJoined::class => $this->removeGame($domainEvent->aggregateId()),
            default => true
        };
    }

    public function isSubscribedTo(StoredEvent $storedEvent): bool
    {
        return true;
    }

    private function saveGame(string $gameId, string $playerId): void
    {
        $this->openGameStore->save(
            new OpenGame($gameId, $playerId)
        );
    }

    private function removeGame(string $gameId): void
    {
        $this->openGameStore->remove($gameId);
    }
}
