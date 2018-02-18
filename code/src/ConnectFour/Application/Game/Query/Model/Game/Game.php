<?php

namespace Gambling\ConnectFour\Application\Game\Query\Model\Game;

use Gambling\Common\EventStore\StoredEvent;

/**
 * This class takes events from the event store and project these to itself.
 * This allows to reuse this class in the projection and if the projection is not available,
 * it can be used to project the game on demand. This can happen when a player joins
 * a game and the game is not in the query store yet. Instead of returning a 404 response,
 * we send the user the latest projection from the event store. This way, the domain model
 * itself stays clean and gets not inflated by a bunch of getters.
 */
final class Game implements \JsonSerializable
{
    /**
     * The game id of the game.
     *
     * @var string
     */
    private $gameId = '';

    /**
     * The assigned chat id of the game.
     *
     * @var string
     */
    private $chatId = '';

    /**
     * The width of the game.
     *
     * @var int
     */
    private $width = 0;

    /**
     * The height of the game.
     *
     * @var int
     */
    private $height = 0;

    /**
     * Tell if the game is finished.
     *
     * @var bool
     */
    private $finished = false;

    /**
     * The moves of the game.
     *
     * @var Move[]
     */
    private $moves = [];

    /**
     * Returns the id.
     *
     * @return string
     */
    public function id(): string
    {
        return $this->gameId;
    }

    /**
     * Returns true if the game is finished.
     *
     * @return bool
     */
    public function finished(): bool
    {
        return $this->finished;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'gameId'   => $this->gameId,
            'chatId'   => $this->chatId,
            'finished' => $this->finished,
            'height'   => $this->height,
            'width'    => $this->width,
            'moves'    => $this->moves
        ];
    }

    /**
     * Apply a stored event. The game can project this to its state.
     * The order of events must be the same as the sequence added to the event store.
     *
     * @param StoredEvent $storedEvent
     */
    public function apply(StoredEvent $storedEvent): void
    {
        $method = 'when' . $storedEvent->name();

        if (method_exists($this, $method)) {
            $this->$method(
                json_decode($storedEvent->payload(), true)
            );
        }
    }

    /**
     * Open the game.
     *
     * @param array $payload
     */
    private function whenGameOpened(array $payload): void
    {
        $this->gameId = $payload['gameId'];
        $this->width = $payload['width'];
        $this->height = $payload['height'];
    }

    /**
     * Project a player movement.
     *
     * @param array $payload
     */
    private function whenPlayerMoved(array $payload): void
    {
        $move = new Move(
            $payload['x'],
            $payload['y'],
            $payload['color']
        );

        if (!in_array($move, $this->moves)) {
            $this->moves[] = $move;
        }
    }

    /**
     * Mark the game as finished.
     *
     * @param array $payload
     */
    private function whenGameAborted(array $payload): void
    {
        $this->finished = true;
    }

    /**
     * Mark the game as finished.
     *
     * @param array $payload
     */
    private function whenGameResigned(array $payload): void
    {
        $this->finished = true;
    }

    /**
     * Mark the game as finished.
     *
     * @param array $payload
     */
    private function whenGameWon(array $payload): void
    {
        $this->finished = true;
    }

    /**
     * Mark the game as finished.
     *
     * @param array $payload
     */
    private function whenGameDrawn(array $payload): void
    {
        $this->finished = true;
    }

    /**
     * Assign the chat id.
     *
     * @param array $payload
     */
    private function whenChatAssigned(array $payload): void
    {
        $this->chatId = $payload['chatId'];
    }
}
