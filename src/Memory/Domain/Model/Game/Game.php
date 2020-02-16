<?php
declare(strict_types=1);

namespace Gaming\Memory\Domain\Model\Game;

use Gaming\Common\Domain\AggregateRoot;
use Gaming\Common\Domain\DomainEvent;
use Gaming\Common\Domain\IsAggregateRoot;
use Gaming\Memory\Domain\Model\Game\Dealer\Dealer;
use Gaming\Memory\Domain\Model\Game\Event\GameClosed;
use Gaming\Memory\Domain\Model\Game\Event\GameOpened;
use Gaming\Memory\Domain\Model\Game\Event\GameStarted;
use Gaming\Memory\Domain\Model\Game\Event\PlayerJoined;
use Gaming\Memory\Domain\Model\Game\Event\PlayerLeaved;
use Gaming\Memory\Domain\Model\Game\Exception\GameNotOpenException;
use Gaming\Memory\Domain\Model\Game\Exception\PlayerAlreadyJoinedException;
use Gaming\Memory\Domain\Model\Game\Exception\PlayerNotAllowedToStartGameException;

/**
 * Everything within this aggregate, except the game itself is a value object.
 */
final class Game implements AggregateRoot
{
    use IsAggregateRoot;

    /**
     * @var GameId
     */
    private GameId $gameId;

    /**
     * @var PlayerPool
     */
    private PlayerPool $playerPool;

    /**
     * @var int[]
     */
    private array $cards;

    /**
     * This is used to determine the state of the game.
     * It's not that complex like in the connect four context,
     * so we leave out the polymorphism and additional mapping stuff.
     *
     * @var int
     */
    private int $state;
    private const STATE_OPEN = 1;
    private const STATE_RUNNING = 2;
    private const STATE_CLOSED = 3;

    /**
     * Game constructor.
     *
     * @param GameId        $gameId
     * @param PlayerPool    $playerPool
     * @param int[]         $cards
     * @param DomainEvent[] $domainEvents
     */
    private function __construct(GameId $gameId, PlayerPool $playerPool, array $cards, array $domainEvents)
    {
        $this->gameId = $gameId;
        $this->playerPool = $playerPool;
        $this->cards = $cards;
        $this->state = self::STATE_OPEN;
        $this->domainEvents = $domainEvents;
    }

    /**
     * Open a new game.
     *
     * @param Dealer $dealer
     * @param string $playerId
     *
     * @return Game
     */
    public static function open(Dealer $dealer, string $playerId): Game
    {
        $gameId = GameId::generate();
        $cards = $dealer->dealIn();
        $player = new Player($playerId);

        return new self(
            $gameId,
            PlayerPool::beginWith($player),
            $cards,
            [
                new GameOpened(
                    $gameId,
                    count($cards),
                    $player
                )
            ]
        );
    }

    /**
     * A player joins in the game.
     *
     * @param string $playerId
     *
     * @throws GameNotOpenException
     * @throws PlayerAlreadyJoinedException
     */
    public function join(string $playerId): void
    {
        if ($this->state !== self::STATE_OPEN) {
            throw new GameNotOpenException();
        }

        $player = new Player($playerId);
        $playerJoined = new PlayerJoined(
            $this->gameId,
            $player
        );

        $this->playerPool = $this->playerPool->join(
            $player
        );
        $this->domainEvents[] = $playerJoined;
    }

    /**
     * A player leaves the game.
     * If the player is the last player, the game gets closed.
     *
     * @param string $playerId
     *
     * @throws Exception\PlayerNotJoinedException
     * @throws GameNotOpenException
     */
    public function leave(string $playerId): void
    {
        if ($this->state !== self::STATE_OPEN) {
            throw new GameNotOpenException();
        }

        $player = new Player($playerId);

        $this->playerPool = $this->playerPool->leave($player);

        $this->domainEvents[] = new PlayerLeaved(
            $this->gameId,
            $player
        );

        if ($this->playerPool->isEmpty()) {
            $this->state = self::STATE_CLOSED;
            $this->domainEvents[] = new GameClosed(
                $this->gameId
            );
        }
    }

    /**
     * The player wants to start the game.
     *
     * @param string $playerId
     *
     * @throws GameNotOpenException
     * @throws PlayerNotAllowedToStartGameException
     */
    public function start(string $playerId): void
    {
        if ($this->state !== self::STATE_OPEN) {
            throw new GameNotOpenException();
        }

        if ($playerId !== $this->playerPool->current()->id()) {
            throw new PlayerNotAllowedToStartGameException();
        }

        $this->state = self::STATE_RUNNING;
        $this->domainEvents[] = new GameStarted(
            $this->gameId,
            $this->playerPool
        );
    }

    /**
     * @return GameId
     */
    public function id(): GameId
    {
        return $this->gameId;
    }
}
