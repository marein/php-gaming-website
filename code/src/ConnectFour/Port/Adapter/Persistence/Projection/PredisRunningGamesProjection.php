<?php

namespace Gambling\ConnectFour\Port\Adapter\Persistence\Projection;

use Gambling\Common\EventStore\StoredEvent;
use Gambling\Common\EventStore\StoredEventSubscriber;
use Predis\Client;

final class PredisRunningGamesProjection implements StoredEventSubscriber
{
    const STORAGE_KEY = 'running-games';

    private const EVENT_TO_METHOD = [
        'ConnectFour.PlayerJoined' => 'handlePlayerJoined',
        'ConnectFour.GameWon'      => 'handleGameFinished',
        'ConnectFour.GameDrawn'    => 'handleGameFinished',
        'ConnectFour.GameAborted'  => 'handleGameFinished'
    ];

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
        $method = self::EVENT_TO_METHOD[$storedEvent->name()] ?? null;

        if ($method) {
            $this->$method($storedEvent);
        }
    }

    /**
     * @inheritdoc
     */
    public function isSubscribedTo(StoredEvent $storedEvent): bool
    {
        return array_key_exists(
            $storedEvent->name(),
            self::EVENT_TO_METHOD
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
    private function handleGameFinished(StoredEvent $storedEvent): void
    {
        $payload = json_decode($storedEvent->payload(), true);
        $gameId = $payload['gameId'];

        $this->predis->srem(self::STORAGE_KEY, $gameId);
    }
}
