<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Exception;
use Gaming\Common\EventStore\EventStore;
use Gaming\Common\EventStore\StoredEvent;
use Gaming\ConnectFour\Application\Game\Query\Exception\GameNotFoundException;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\Game;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\GameFinder;
use Gaming\ConnectFour\Domain\Game\GameId;

final class EventStoreGameFinder implements GameFinder
{
    private EventStore $eventStore;

    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    public function find(string $gameId): Game
    {
        $this->throwExceptionOnInvalidGameId($gameId);

        $storedEvents = $this->eventStore->byAggregateId(
            $gameId
        );

        if (empty($storedEvents)) {
            throw new GameNotFoundException();
        }

        return $this->reconstituteGameFromStoredEvents($storedEvents);
    }

    /**
     * @param StoredEvent[] $storedEvents
     */
    private function reconstituteGameFromStoredEvents(array $storedEvents): Game
    {
        $game = new Game();

        foreach ($storedEvents as $storedEvent) {
            $game->apply($storedEvent->domainEvent());
        }

        return $game;
    }

    /**
     * @throws GameNotFoundException
     */
    private function throwExceptionOnInvalidGameId(string $gameId): void
    {
        try {
            GameId::fromString($gameId);
        } catch (Exception $exception) {
            throw new GameNotFoundException();
        }
    }
}
