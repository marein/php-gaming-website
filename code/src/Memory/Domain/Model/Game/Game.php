<?php
declare(strict_types=1);

namespace Gambling\Memory\Domain\Model\Game;

use Gambling\Common\Domain\AggregateRoot;
use Gambling\Common\Domain\DomainEvent;
use Gambling\Common\Domain\IsAggregateRoot;
use Gambling\Memory\Domain\Model\Game\Dealer\Dealer;
use Gambling\Memory\Domain\Model\Game\Event\GameOpened;

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
     * @return GameId
     */
    public function id(): GameId
    {
        return $this->gameId;
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
}
