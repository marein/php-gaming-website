<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Projection;

use Gaming\Common\EventStore\StoredEvent;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GamesByPlayerStore;
use Gaming\ConnectFour\Domain\Game\Event\GameAborted;
use Gaming\ConnectFour\Domain\Game\Event\PlayerJoined;

final class GamesByPlayerProjection implements StoredEventSubscriber
{
    public function __construct(
        private readonly GamesByPlayerStore $gamesByPlayerStore
    ) {
    }

    public function handle(StoredEvent $storedEvent): void
    {
        $domainEvent = $storedEvent->domainEvent();

        match ($domainEvent::class) {
            PlayerJoined::class => $this->handlePlayerJoined($domainEvent),
            GameAborted::class => $this->handleGameAborted($domainEvent),
            default => true
        };
    }

    private function handlePlayerJoined(PlayerJoined $playerJoined): void
    {
        $this->gamesByPlayerStore->addToPlayer(
            $playerJoined->joinedPlayerId(),
            $playerJoined->aggregateId()
        );

        $this->gamesByPlayerStore->addToPlayer(
            $playerJoined->opponentPlayerId(),
            $playerJoined->aggregateId()
        );
    }

    private function handleGameAborted(GameAborted $gameAborted): void
    {
        // We're only interested in running games.
        if ($gameAborted->opponentPlayerId() === '') {
            return;
        }

        $this->gamesByPlayerStore->removeFromPlayer(
            $gameAborted->abortedPlayerId(),
            $gameAborted->aggregateId()
        );

        $this->gamesByPlayerStore->removeFromPlayer(
            $gameAborted->opponentPlayerId(),
            $gameAborted->aggregateId()
        );
    }
}
