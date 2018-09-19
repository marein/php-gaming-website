<?php
declare(strict_types=1);

namespace Gambling\ConnectFour\Port\Adapter\Persistence\Projection;

use Gambling\Common\EventStore\StoredEvent;
use Gambling\Common\EventStore\StoredEventSubscriber;
use Gambling\ConnectFour\Application\Game\Query\Model\Game\Game;
use Predis\Client;

/**
 * This class delegates the projection to the model. After the model project the current event,
 * it gets stored with serialize(). This process is a bit slow but allows lesser code to write.
 * If this becomes a bottleneck, we can serialize it as json at a later stage. A performance
 * test shows, this class can project around 4000 games in a second.
 *
 * An serialized game is around 13 kilobyte in size. This is huge, compared to json with only 1.3 kilobyte.
 * Each game gets saved after each event. We can pool the changes and push only the latest to redis. But as
 * stated above, we can do further improvements when we hit a bottleneck.
 *
 * If the model changes, we rebuild the projection anyway, so I have no fear about using serialize() here.
 */
final class PredisGameProjection implements StoredEventSubscriber
{
    const STORAGE_KEY_PREFIX = 'game.';

    /**
     * @var Client
     */
    private $predis;

    /**
     * This could be a memory leak. A game gets deleted after it is finished.
     * If we don't run thousands of games, it shouldn't be hit the memory limit.
     *
     * @var Game[]
     */
    private $games;

    /**
     * PredisGameProjection constructor.
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
        $payload = json_decode($storedEvent->payload(), true);

        $game = $this->get($payload['gameId']);
        $game->apply($storedEvent);

        $this->set($game);
        $this->clearGameFromCacheIfFinished($game);
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
                'PlayerJoined',
                'PlayerMoved',
                'GameWon',
                'GameDrawn',
                'GameAborted',
                'ChatAssigned'
            ]
        );
    }

    /**
     * Get a game from the game cache, from the store or create a new one.
     *
     * @param string $gameId
     *
     * @return Game
     */
    private function get(string $gameId): Game
    {
        if (!isset($this->games[$gameId])) {
            $serializedGame = $this->predis->get(
                self::STORAGE_KEY_PREFIX . $gameId
            );

            $this->games[$gameId] = $serializedGame ? unserialize($serializedGame) : new Game();
        }

        return $this->games[$gameId];
    }

    /**
     * Persist the game to the store.
     *
     * @param Game $game
     */
    private function set(Game $game): void
    {
        $this->predis->set(
            self::STORAGE_KEY_PREFIX . $game->id(),
            serialize($game)
        );
    }

    /**
     * Clear the game from cache if it is finished.
     *
     * @param Game $game
     */
    private function clearGameFromCacheIfFinished(Game $game): void
    {
        if ($game->finished()) {
            unset($this->games[$game->id()]);
        }
    }
}
