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
                'GameOpened',
                'GameAborted',
                'PlayerJoined'
            ]
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleGameOpened(array $payload): void
    {
        $this->openGameStore->save(
            new OpenGame($payload['gameId'], $payload['playerId'])
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleGameAborted(array $payload): void
    {
        $this->openGameStore->remove($payload['gameId']);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handlePlayerJoined(array $payload): void
    {
        $this->openGameStore->remove($payload['gameId']);
    }
}
