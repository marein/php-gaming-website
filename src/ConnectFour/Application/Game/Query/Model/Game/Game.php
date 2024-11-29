<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\Model\Game;

use Gaming\ConnectFour\Domain\Game\Event\ChatAssigned;
use Gaming\ConnectFour\Domain\Game\Event\GameAborted;
use Gaming\ConnectFour\Domain\Game\Event\GameDrawn;
use Gaming\ConnectFour\Domain\Game\Event\GameOpened;
use Gaming\ConnectFour\Domain\Game\Event\GameResigned;
use Gaming\ConnectFour\Domain\Game\Event\GameWon;
use Gaming\ConnectFour\Domain\Game\Event\PlayerJoined;
use Gaming\ConnectFour\Domain\Game\Event\PlayerMoved;
use Gaming\ConnectFour\Domain\Game\WinningRule\WinningSequence;
use JsonSerializable;
use RuntimeException;

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
     * @var WinningSequence[]
     */
    private array $winningSequences = [];

    /**
     * @var Move[]
     */
    private array $moves = [];

    public function id(): string
    {
        return $this->gameId;
    }

    public function chatId(): string
    {
        return $this->chatId;
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
            'moves' => $this->moves,
            'winningSequences' => $this->winningSequences
        ];
    }

    /**
     * Apply a domain event. The game can project this to its state.
     * The order of events must be the same as the sequence added to the event store.
     */
    public function apply(object $domainEvent): void
    {
        match ($domainEvent::class) {
            GameOpened::class => $this->handleGameOpened($domainEvent),
            PlayerJoined::class => $this->handlePlayerJoined($domainEvent),
            PlayerMoved::class => $this->handlePlayerMoved($domainEvent),
            GameAborted::class,
            GameDrawn::class,
            GameResigned::class => $this->markAsFinished(),
            GameWon::class => $this->handleGameWon($domainEvent),
            ChatAssigned::class => $this->handleChatAssigned($domainEvent),
            default => throw new RuntimeException($domainEvent::class . ' must be handled.')
        };
    }

    private function handleGameOpened(GameOpened $gameOpened): void
    {
        $this->gameId = $gameOpened->aggregateId();
        $this->width = $gameOpened->width();
        $this->height = $gameOpened->height();
        $this->addPlayer($gameOpened->playerId());
    }

    private function handlePlayerJoined(PlayerJoined $playerJoined): void
    {
        $this->addPlayer($playerJoined->joinedPlayerId());
    }

    private function handlePlayerMoved(PlayerMoved $playerMoved): void
    {
        $move = new Move(
            $playerMoved->x(),
            $playerMoved->y(),
            $playerMoved->color()
        );

        if (!in_array($move, $this->moves)) {
            $this->moves[] = $move;
        }
    }

    private function handleGameWon(GameWon $gameWon): void
    {
        $this->winningSequences = $gameWon->winningSequences();

        $this->markAsFinished();
    }

    private function handleChatAssigned(ChatAssigned $chatAssigned): void
    {
        $this->chatId = $chatAssigned->chatId();
    }

    private function markAsFinished(): void
    {
        $this->finished = true;
    }

    private function addPlayer(string $playerId): void
    {
        if (!in_array($playerId, $this->players)) {
            $this->players[] = $playerId;
        }
    }
}
