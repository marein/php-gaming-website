<?php
declare(strict_types=1);

namespace Gambling\ConnectFour\Port\Adapter\Persistence\Projection;

use Gambling\Common\EventStore\StoredEvent;
use Gambling\Common\EventStore\StoredEventSubscriber;
use Predis\Client;

final class PredisGamesByPlayerProjection implements StoredEventSubscriber
{
    const STORAGE_KEY_PREFIX = 'games-by-player.';

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
        $gameId = $payload['gameId'];
        $joinedPlayerId = $payload['joinedPlayerId'];
        $opponentPlayerId = $payload['opponentPlayerId'];

        $key = self::STORAGE_KEY_PREFIX . $joinedPlayerId;
        $this->predis->lpush($key, [$gameId]);

        $key = self::STORAGE_KEY_PREFIX . $opponentPlayerId;
        $this->predis->lpush($key, [$gameId]);
    }

    /**
     * @param StoredEvent $storedEvent
     */
    private function handleGameAborted(StoredEvent $storedEvent): void
    {
        $payload = json_decode($storedEvent->payload(), true);
        $gameId = $payload['gameId'];
        $abortedPlayerId = $payload['abortedPlayerId'];
        $opponentPlayerId = $payload['opponentPlayerId'];

        $key = self::STORAGE_KEY_PREFIX . $abortedPlayerId;
        $this->predis->lrem($key, 0, $gameId);

        if ($opponentPlayerId !== '') {
            $key = self::STORAGE_KEY_PREFIX . $opponentPlayerId;
            $this->predis->lrem($key, 0, $gameId);
        }
    }
}
