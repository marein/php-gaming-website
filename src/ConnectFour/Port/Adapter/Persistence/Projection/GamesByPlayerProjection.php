<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Projection;

use Gaming\Common\EventStore\StoredEvent;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GamesByPlayerStore;

final class GamesByPlayerProjection implements StoredEventSubscriber
{
    /**
     * @var GamesByPlayerStore
     */
    private $gamesByPlayerStore;

    /**
     * GamesByPlayerProjection constructor.
     *
     * @param GamesByPlayerStore $gamesByPlayerStore
     */
    public function __construct(GamesByPlayerStore $gamesByPlayerStore)
    {
        $this->gamesByPlayerStore = $gamesByPlayerStore;
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
                'GameAborted',
                'PlayerJoined'
            ]
        );
    }

    /**
     * @param StoredEvent $storedEvent
     */
    private function handlePlayerJoined(StoredEvent $storedEvent): void
    {
        $payload = json_decode($storedEvent->payload(), true);

        $this->gamesByPlayerStore->addToPlayer(
            $payload['joinedPlayerId'],
            $payload['gameId']
        );

        $this->gamesByPlayerStore->addToPlayer(
            $payload['opponentPlayerId'],
            $payload['gameId']
        );
    }

    /**
     * @param StoredEvent $storedEvent
     */
    private function handleGameAborted(StoredEvent $storedEvent): void
    {
        $payload = json_decode($storedEvent->payload(), true);

        // We're only interested in running games.
        if ($payload['opponentPlayerId'] === '') {
            return;
        }

        $this->gamesByPlayerStore->removeFromPlayer(
            $payload['abortedPlayerId'],
            $payload['gameId']
        );

        $this->gamesByPlayerStore->removeFromPlayer(
            $payload['opponentPlayerId'],
            $payload['gameId']
        );
    }
}
