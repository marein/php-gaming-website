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
use RuntimeException;

/**
 * This class takes events from the event store and project these to itself.
 * This allows to reuse this class in the projection and if the projection is not available,
 * it can be used to project the game on demand. This can happen when a player joins
 * a game and the game is not in the query store yet. Instead of returning a 404 response,
 * we send the user the latest projection from the event store. This way, the domain model
 * itself stays clean and gets not inflated by a bunch of getters.
 */
final class Game
{
    public const STATE_OPEN = 'open';
    public const STATE_RUNNING = 'running';
    public const STATE_DRAW = 'draw';
    public const STATE_FINISHED = 'finished';

    /**
     * @param Move[] $moves
     * @param WinningSequence[] $winningSequences
     */
    public function __construct(
        public private(set) string $gameId = '',
        public private(set) string $chatId = '',
        public private(set) string $openedBy = '',
        public private(set) string $redPlayerId = '',
        public private(set) string $yellowPlayerId = '',
        public private(set) string $currentPlayerId = '',
        public private(set) string $winnerId = '',
        public private(set) string $loserId = '',
        public private(set) string $resignedBy = '',
        public private(set) string $abortedBy = '',
        public private(set) string $state = self::STATE_OPEN,
        public private(set) int $height = 0,
        public private(set) int $width = 0,
        public private(set) ?int $preferredStone = null,
        public private(set) array $moves = [],
        public private(set) array $winningSequences = []
    ) {
    }

    /**
     * @deprecated Use property instead.
     */
    public function id(): string
    {
        return $this->gameId;
    }

    /**
     * @deprecated Use property instead.
     */
    public function finished(): bool
    {
        return $this->state === self::STATE_FINISHED || $this->state === self::STATE_DRAW;
    }

    /**
     * @return string[]
     */
    public function players(): array
    {
        return array_filter([$this->redPlayerId, $this->yellowPlayerId]);
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
            GameAborted::class => $this->handleGameAborted($domainEvent),
            GameDrawn::class => $this->handleGameDrawn($domainEvent),
            GameResigned::class => $this->handleGameResigned($domainEvent),
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
        $this->preferredStone = $gameOpened->preferredStone;
        $this->openedBy = $gameOpened->playerId();
    }

    private function handlePlayerJoined(PlayerJoined $playerJoined): void
    {
        $this->currentPlayerId = $this->redPlayerId = $playerJoined->redPlayerId;
        $this->yellowPlayerId = $playerJoined->yellowPlayerId;

        $this->state = self::STATE_RUNNING;
    }

    private function handlePlayerMoved(PlayerMoved $playerMoved): void
    {
        $this->currentPlayerId = $playerMoved->nextPlayerId;

        $move = new Move(
            $playerMoved->x(),
            $playerMoved->y(),
            $playerMoved->color()
        );

        if (!in_array($move, $this->moves)) {
            $this->moves[] = $move;
        }
    }

    private function handleGameAborted(GameAborted $gameAborted): void
    {
        $this->abortedBy = $gameAborted->abortedPlayerId();

        $this->markAsFinished();
    }

    private function handleGameDrawn(GameDrawn $gameDrawn): void
    {
        $this->markAsFinished(self::STATE_DRAW);
    }

    private function handleGameResigned(GameResigned $gameResigned): void
    {
        $this->winnerId = $gameResigned->opponentPlayerId();
        $this->resignedBy = $gameResigned->resignedPlayerId();

        $this->markAsFinished();
    }

    private function handleGameWon(GameWon $gameWon): void
    {
        $this->winningSequences = $gameWon->winningSequences();
        $this->winnerId = $gameWon->winnerId;
        $this->loserId = $gameWon->loserId;

        $this->markAsFinished();
    }

    private function handleChatAssigned(ChatAssigned $chatAssigned): void
    {
        $this->chatId = $chatAssigned->chatId();
    }

    private function markAsFinished(string $state = self::STATE_FINISHED): void
    {
        $this->state = $state;
        $this->currentPlayerId = '';
    }
}
