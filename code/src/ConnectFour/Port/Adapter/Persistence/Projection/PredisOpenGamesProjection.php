<?php

namespace Gambling\ConnectFour\Port\Adapter\Persistence\Projection;

use Gambling\Common\EventStore\StoredEvent;
use Gambling\Common\EventStore\StoredEventSubscriber;
use Predis\Client;

final class PredisOpenGamesProjection implements StoredEventSubscriber
{
    const STORAGE_KEY = 'open-games';

    private const EVENT_TO_METHOD = [
        'connect-four.game-opened'   => 'handleGameOpened',
        'connect-four.game-aborted'  => 'handleGameAborted',
        'connect-four.player-joined' => 'handlePlayerJoined'
    ];

    /**
     * @var Client
     */
    private $predis;

    /**
     * PredisOpenGamesProjection constructor.
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

    private function handleGameOpened(StoredEvent $storedEvent): void
    {
        $payload = json_decode($storedEvent->payload(), true);
        $gameId = $payload['gameId'];
        $playerId = $payload['playerId'];

        $this->predis->hset(
            self::STORAGE_KEY,
            $gameId,
            json_encode([
                'gameId'   => $gameId,
                'playerId' => $playerId
            ])
        );
    }

    private function handleGameAborted(StoredEvent $storedEvent): void
    {
        $payload = json_decode($storedEvent->payload(), true);
        $gameId = $payload['gameId'];

        $this->predis->hdel(self::STORAGE_KEY, [$gameId]);
    }

    private function handlePlayerJoined(StoredEvent $storedEvent): void
    {
        $payload = json_decode($storedEvent->payload(), true);
        $gameId = $payload['gameId'];

        $this->predis->hdel(self::STORAGE_KEY, [$gameId]);
    }
}
