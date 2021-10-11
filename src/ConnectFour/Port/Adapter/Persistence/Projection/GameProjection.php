<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Projection;

use Gaming\Common\EventStore\StoredEvent;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\Game;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\GameStore;
use Gaming\ConnectFour\Port\Adapter\Persistence\Repository\InMemoryCacheGameStore;

final class GameProjection implements StoredEventSubscriber
{
    private GameStore $gameStore;

    public function __construct(GameStore $gameStore)
    {
        $this->gameStore = new InMemoryCacheGameStore(
            $gameStore,
            1000
        );
    }

    public function handle(StoredEvent $storedEvent): void
    {
        if ($storedEvent->name() === 'GameOpened') {
            $game = new Game();
        } else {
            $payload = json_decode($storedEvent->payload(), true, 512, JSON_THROW_ON_ERROR);
            $game = $this->gameStore->find($payload['gameId']);
        }

        $game->apply($storedEvent);

        $this->gameStore->save($game);
    }

    public function isSubscribedTo(StoredEvent $storedEvent): bool
    {
        return in_array(
            $storedEvent->name(),
            [
                'GameOpened',
                'PlayerJoined',
                'PlayerMoved',
                'GameWon',
                'GameDrawn',
                'GameAborted',
                'ChatAssigned'
            ]
        );
    }
}
