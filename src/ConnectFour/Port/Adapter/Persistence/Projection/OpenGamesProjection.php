<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Projection;

use Gaming\Common\EventStore\StoredEvent;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\ConnectFour\Application\Game\Query\Model\OpenGames\OpenGame;
use Gaming\ConnectFour\Application\Game\Query\Model\OpenGames\OpenGameStore;

final class OpenGamesProjection implements StoredEventSubscriber
{
    /**
     * @var OpenGameStore
     */
    private OpenGameStore $openGameStore;

    /**
     * OpenGamesProjection constructor.
     *
     * @param OpenGameStore $openGameStore
     */
    public function __construct(OpenGameStore $openGameStore)
    {
        $this->openGameStore = $openGameStore;
    }

    /**
     * @inheritdoc
     */
    public function handle(StoredEvent $storedEvent): void
    {
        $this->{'handle' . $storedEvent->name()}($storedEvent);
    }

    /**
     * @inheritdoc
     */
    public function isSubscribedTo(StoredEvent $storedEvent): bool
    {
        return in_array(
            $storedEvent->name(),
            [
                'GameOpened',
                'GameAborted',
                'PlayerJoined'
            ]
        );
    }

    /**
     * @param StoredEvent $storedEvent
     */
    private function handleGameOpened(StoredEvent $storedEvent): void
    {
        $payload = json_decode($storedEvent->payload(), true);

        $this->openGameStore->save(
            new OpenGame($payload['gameId'], $payload['playerId'])
        );
    }

    /**
     * @param StoredEvent $storedEvent
     */
    private function handleGameAborted(StoredEvent $storedEvent): void
    {
        $payload = json_decode($storedEvent->payload(), true);

        $this->openGameStore->remove($payload['gameId']);
    }

    /**
     * @param StoredEvent $storedEvent
     */
    private function handlePlayerJoined(StoredEvent $storedEvent): void
    {
        $payload = json_decode($storedEvent->payload(), true);

        $this->openGameStore->remove($payload['gameId']);
    }
}
