<?php
declare(strict_types=1);

namespace Gambling\ConnectFour\Port\Adapter\Persistence\Projection;

use Gambling\Common\EventStore\StoredEvent;
use Gambling\Common\EventStore\StoredEventSubscriber;
use Predis\Client;

final class PredisRunningGamesProjection implements StoredEventSubscriber
{
    const STORAGE_KEY = 'running-games';

    /**
     * @var Client
     */
    private $predis;

    /**
     * PredisRunningGamesProjection constructor.
     *
     * @param Client $predis
     */
    public function __construct(Client $predis)
    {
        $this->predis = $predis;
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
        $gameId = $payload['gameId'];

        $this->predis->sadd(self::STORAGE_KEY, $gameId);
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
        $gameId = $payload['gameId'];

        $this->predis->srem(self::STORAGE_KEY, $gameId);
    }
}
