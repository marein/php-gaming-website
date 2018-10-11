<?php
declare(strict_types=1);

namespace Gaming\Memory\Domain\Model\Game;

use Gaming\Common\Domain\AggregateRoot;
use Gaming\Common\Domain\DomainEvent;
use Gaming\Common\Domain\IsAggregateRoot;
use Gaming\Memory\Domain\Model\Game\Dealer\Dealer;
use Gaming\Memory\Domain\Model\Game\Event\GameOpened;
use Gaming\Memory\Domain\Model\Game\Event\PlayerJoined;
use Gaming\Memory\Domain\Model\Game\Exception\PlayerAlreadyJoinedException;

/**
 * Unlike in the connect four context, this game does not use the state pattern.
 * There is not much going on here.
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
     * @var PlayerPool
     */
    private $playerPool;

    /**
     * @var int[]
     */
    private $cards;

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
     * @throws PlayerAlreadyJoinedException
     */
    public function join(string $playerId): void
    {
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
     * @return GameId
     */
    public function id(): GameId
    {
        return $this->gameId;
    }
}
