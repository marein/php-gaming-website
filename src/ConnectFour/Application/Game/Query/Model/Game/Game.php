<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\Model\Game;

use Gaming\Common\EventStore\StoredEvent;
use JsonSerializable;

/**
 * This class takes events from the event store and project these to itself.
 * This allows to reuse this class in the projection and if the projection is not available,
 * it can be used to project the game on demand. This can happen when a player joins
 * a game and the game is not in the query store yet. Instead of returning a 404 response,
 * we send the user the latest projection from the event store. This way, the domain model
 * itself stays clean and gets not inflated by a bunch of getters.
 */
final class Game implements JsonSerializable
{
    /**
     * The game id of the game.
     *
     * @var string
     */
    private string $gameId = '';

    /**
     * The assigned chat id of the game.
     *
     * @var string
     */
    private string $chatId = '';

    /**
     * The players of the game.
     *
     * @var string[]
     */
    private array $players = [];

    /**
     * The width of the game.
     *
     * @var int
     */
    private int $width = 0;

    /**
     * The height of the game.
     *
     * @var int
     */
    private int $height = 0;

    /**
     * Tell if the game is finished.
     *
     * @var bool
     */
    private bool $finished = false;

    /**
     * The moves of the game.
     *
     * @var Move[]
     */
    private array $moves = [];

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

    public function jsonSerialize(): mixed
    {
        return [
            'gameId' => $this->gameId,
            'chatId' => $this->chatId,
            'players' => $this->players,
            'finished' => $this->finished,
            'height' => $this->height,
            'width' => $this->width,
            'moves' => $this->moves
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
                json_decode($storedEvent->payload(), true, 512, JSON_THROW_ON_ERROR)
            );
        }
    }

    /**
     * Open the game.
     *
     * @param array<string, mixed> $payload
     */
    private function whenGameOpened(array $payload): void
    {
        $this->gameId = $payload['gameId'];
        $this->width = $payload['width'];
        $this->height = $payload['height'];
        $this->addPlayer($payload['playerId']);
    }

    /**
     * Assign the joined player.
     *
     * @param array<string, mixed> $payload
     */
    private function whenPlayerJoined(array $payload): void
    {
        $this->addPlayer($payload['joinedPlayerId']);
    }

    /**
     * Project a player movement.
     *
     * @param array<string, mixed> $payload
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
     * @param array<string, mixed> $payload
     */
    private function whenGameAborted(array $payload): void
    {
        $this->finished = true;
    }

    /**
     * Mark the game as finished.
     *
     * @param array<string, mixed> $payload
     */
    private function whenGameResigned(array $payload): void
    {
        $this->finished = true;
    }

    /**
     * Mark the game as finished.
     *
     * @param array<string, mixed> $payload
     */
    private function whenGameWon(array $payload): void
    {
        $this->finished = true;
    }

    /**
     * Mark the game as finished.
     *
     * @param array<string, mixed> $payload
     */
    private function whenGameDrawn(array $payload): void
    {
        $this->finished = true;
    }

    /**
     * Assign the chat id.
     *
     * @param array<string, mixed> $payload
     */
    private function whenChatAssigned(array $payload): void
    {
        $this->chatId = $payload['chatId'];
    }

    /**
     * This is an idempotent operation. If an event occurs twice, it's ignored.
     *
     * @param string $playerId
     */
    private function addPlayer(string $playerId): void
    {
        if (!in_array($playerId, $this->players)) {
            $this->players[] = $playerId;
        }
    }
}
