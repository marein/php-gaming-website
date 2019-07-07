<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Projection;

use Gaming\Common\EventStore\StoredEvent;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\ConnectFour\Application\Game\Query\Model\RunningGames\RunningGameStore;

final class RunningGamesProjection implements StoredEventSubscriber
{
    /**
     * @var RunningGameStore
     */
    private $runningGameStore;

    /**
     * RunningGamesProjection constructor.
     *
     * @param RunningGameStore $runningGameStore
     */
    public function __construct(RunningGameStore $runningGameStore)
    {
        $this->runningGameStore = $runningGameStore;
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
                'PlayerJoined',
                'GameWon',
                'GameDrawn',
                'GameAborted',
                'GameResigned'
            ]
        );
    }

    /**
     * @param StoredEvent $storedEvent
     */
    private function handlePlayerJoined(StoredEvent $storedEvent): void
    {
        $payload = json_decode($storedEvent->payload(), true);

        $this->runningGameStore->add($payload['gameId']);
    }

    /**
     * @param StoredEvent $storedEvent
     */
    private function handleGameAborted(StoredEvent $storedEvent): void
    {
        $this->handleGameFinished($storedEvent);
    }

    /**
     * @param StoredEvent $storedEvent
     */
    private function handleGameResigned(StoredEvent $storedEvent): void
    {
        $this->handleGameFinished($storedEvent);
    }

    /**
     * @param StoredEvent $storedEvent
     */
    private function handleGameWon(StoredEvent $storedEvent): void
    {
        $this->handleGameFinished($storedEvent);
    }

    /**
     * @param StoredEvent $storedEvent
     */
    private function handleGameDrawn(StoredEvent $storedEvent): void
    {
        $this->handleGameFinished($storedEvent);
    }

    /**
     * @param StoredEvent $storedEvent
     */
    private function handleGameFinished(StoredEvent $storedEvent): void
    {
        $payload = json_decode($storedEvent->payload(), true);

        $this->runningGameStore->remove($payload['gameId']);
    }
}
