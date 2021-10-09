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
    private string $gameId = '';

    private string $chatId = '';

    /**
     * @var string[]
     */
    private array $players = [];

    private int $width = 0;

    private int $height = 0;

    private bool $finished = false;

    /**
     * @var Move[]
     */
    private array $moves = [];

    public function id(): string
    {
        return $this->gameId;
    }

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
     * @param array<string, mixed> $payload
     */
    private function whenPlayerJoined(array $payload): void
    {
        $this->addPlayer($payload['joinedPlayerId']);
    }

    /**
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
     * @param array<string, mixed> $payload
     */
    private function whenGameAborted(array $payload): void
    {
        $this->finished = true;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function whenGameResigned(array $payload): void
    {
        $this->finished = true;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function whenGameWon(array $payload): void
    {
        $this->finished = true;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function whenGameDrawn(array $payload): void
    {
        $this->finished = true;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function whenChatAssigned(array $payload): void
    {
        $this->chatId = $payload['chatId'];
    }

    private function addPlayer(string $playerId): void
    {
        if (!in_array($playerId, $this->players)) {
            $this->players[] = $playerId;
        }
    }
}
