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
    private RunningGameStore $runningGameStore;

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
        $this->{'handle' . $storedEvent->name()}(
            json_decode($storedEvent->payload(), true, 512, JSON_THROW_ON_ERROR)
        );
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
     * @param array<string, mixed> $payload
     */
    private function handlePlayerJoined(array $payload): void
    {
        $this->runningGameStore->add($payload['gameId']);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleGameAborted(array $payload): void
    {
        $this->handleGameFinished($payload);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleGameResigned(array $payload): void
    {
        $this->handleGameFinished($payload);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleGameWon(array $payload): void
    {
        $this->handleGameFinished($payload);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleGameDrawn(array $payload): void
    {
        $this->handleGameFinished($payload);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleGameFinished(array $payload): void
    {
        $this->runningGameStore->remove($payload['gameId']);
    }
}
