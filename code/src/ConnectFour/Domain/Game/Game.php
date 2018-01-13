<?php

namespace Gambling\ConnectFour\Domain\Game;

use Gambling\Common\Domain\AggregateRoot;
use Gambling\Common\Domain\DomainEvent;
use Gambling\Common\Domain\IsAggregateRoot;
use Gambling\ConnectFour\Domain\Game\Board\Stone;
use Gambling\ConnectFour\Domain\Game\Event\ChatAssigned;
use Gambling\ConnectFour\Domain\Game\Event\GameOpened;
use Gambling\ConnectFour\Domain\Game\Exception\GameException;
use Gambling\ConnectFour\Domain\Game\State\Open;
use Gambling\ConnectFour\Domain\Game\State\State;
use Gambling\ConnectFour\Domain\Game\State\Transition;

/**
 * The game uses the state pattern to get rid of the conditional mess for each state.
 *
 * Everything within this aggregate, except the game itself is a value object.
 */
final class Game implements AggregateRoot
{
    use IsAggregateRoot;

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
        $this->domainEvents = $domainEvents;
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

        return new self(
            $gameId,
            new Open(
                $configuration,
                $player
            ),
            [
                new GameOpened(
                    $gameId,
                    $size,
                    $player
                )
            ]
        );
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
        $transition = $this->state->move($this->id(), $playerId, $column);

        $this->applyTransition($transition);
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
        $transition = $this->state->join($this->id(), $playerId);

        $this->applyTransition($transition);
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
        $transition = $this->state->abort($this->id(), $playerId);

        $this->applyTransition($transition);
    }

    /**
     * The given player resigns the game.
     *
     * @param string $playerId
     *
     * @throws GameException
     */
    public function resign(string $playerId): void
    {
        $transition = $this->state->resign($this->id(), $playerId);

        $this->applyTransition($transition);
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
     * Apply a state transition.
     *
     * @param Transition $transition
     */
    private function applyTransition(Transition $transition): void
    {
        $this->state = $transition->state();

        $this->domainEvents = array_merge(
            $this->domainEvents,
            $transition->domainEvents()
        );
    }
}
