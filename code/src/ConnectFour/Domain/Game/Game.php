<?php

namespace Gambling\ConnectFour\Domain\Game;

use Gambling\Common\Domain\AggregateRoot;
use Gambling\Common\Domain\DomainEvent;
use Gambling\ConnectFour\Domain\Game\Board\Stone;
use Gambling\ConnectFour\Domain\Game\Event\ChatAssigned;
use Gambling\ConnectFour\Domain\Game\Event\GameOpened;
use Gambling\ConnectFour\Domain\Game\Exception\GameException;
use Gambling\ConnectFour\Domain\Game\State\Aborted;
use Gambling\ConnectFour\Domain\Game\State\Drawn;
use Gambling\ConnectFour\Domain\Game\State\Open;
use Gambling\ConnectFour\Domain\Game\State\Running;
use Gambling\ConnectFour\Domain\Game\State\State;
use Gambling\ConnectFour\Domain\Game\State\Won;
use Marein\FriendVisibility\HasFriendClasses;

/**
 * The game uses the state pattern to get rid of the conditional mess for each state.
 * I use the marein/php-friend-visibility package, so the defined friend classes (in this case the states)
 * can access the private members of the game directly. The states are definitely part of the game,
 * so its plausible they know the internals of this class. Other techniques I thought of is return a
 * Transition object with the occurred domain events and the new state, but this ends up with more code.
 * I think this approach is easier to understand if you grasp the concept of friends.
 *
 * Everything within this aggregate, except the game itself is a value object.
 */
final class Game implements AggregateRoot
{
    use HasFriendClasses;

    /**
     * @var GameId
     */
    private $gameId;

    /**
     * @var State
     */
    private $state;

    /**
     * @var string
     */
    private $chatId;

    /**
     * @var DomainEvent[]
     */
    private $domainEvents;

    /**
     * Game constructor.
     *
     * @param GameId        $gameId
     * @param State         $state
     * @param DomainEvent[] $domainEvents
     */
    private function __construct(GameId $gameId, State $state, array $domainEvents)
    {
        $this->gameId = $gameId;
        $this->state = $state;
        $this->domainEvents = new \ArrayObject($domainEvents);
        $this->chatId = '';
    }

    /**
     * @return GameId
     */
    public function id(): GameId
    {
        return $this->gameId;
    }

    /**
     * @param Configuration $configuration
     * @param string        $playerId
     *
     * @return Game
     */
    public static function open(Configuration $configuration, string $playerId): Game
    {
        $gameId = GameId::generate();
        $size = $configuration->size();
        $player = new Player($playerId, Stone::red());
        $state = new Open(
            $configuration,
            $player
        );
        $domainEvents = [
            new GameOpened(
                $gameId,
                $size,
                $player
            )
        ];
        $game = new self($gameId, $state, $domainEvents);

        return $game;
    }

    /**
     * The given player makes the move in the given column.
     *
     * @param string $playerId
     * @param int    $column
     *
     * @throws GameException
     */
    public function move(string $playerId, int $column): void
    {
        $this->state->move($this, $playerId, $column);
    }

    /**
     * The given player joins the game.
     *
     * @param string $playerId
     *
     * @throws GameException
     */
    public function join(string $playerId): void
    {
        $this->state->join($this, $playerId);
    }

    /**
     * The given player aborts the game.
     *
     * @param string $playerId
     *
     * @throws GameException
     */
    public function abort(string $playerId): void
    {
        $this->state->abort($this, $playerId);
    }

    /**
     * Assign the chat to the game.
     *
     * @param string $chatId
     */
    public function assignChat(string $chatId): void
    {
        // This is an idempotent operation.
        if ($this->chatId === '') {
            $this->chatId = $chatId;
            $this->domainEvents[] = new ChatAssigned(
                $this->gameId,
                $this->chatId
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function flushDomainEvents(): array
    {
        $domainEvents = $this->domainEvents->getArrayCopy();

        $this->domainEvents = new \ArrayObject();

        return $domainEvents;
    }

    /**
     * @inheritdoc
     */
    protected static function friendClasses(): array
    {
        return [
            Aborted::class,
            Drawn::class,
            Open::class,
            Running::class,
            Won::class
        ];
    }
}
