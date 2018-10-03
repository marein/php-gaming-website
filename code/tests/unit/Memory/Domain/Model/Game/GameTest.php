<?php
declare(strict_types=1);

namespace Gaming\Memory\Domain\Model\Game;

use Gaming\Memory\Domain\Model\Game\Dealer\LazyDealer;
use Gaming\Memory\Domain\Model\Game\Event\GameOpened;
use Gaming\Memory\Domain\Model\Game\Event\PlayerJoined;
use PHPUnit\Framework\TestCase;

class GameTest extends TestCase
{
    /**
     * @test
     */
    public function aGameCanBeOpened(): void
    {
        $game = Game::open(
            new LazyDealer(5),
            'playerId1'
        );

        $domainEvents = $game->flushDomainEvents();
        $gameOpened = $domainEvents[0];

        $this->assertCount(1, $domainEvents);
        $this->assertInstanceOf(GameOpened::class, $gameOpened);
        $this->assertSame($game->id()->toString(), $gameOpened->aggregateId());
        $this->assertSame(10, $gameOpened->payload()['numberOfCards']);
        $this->assertSame('playerId1', $gameOpened->payload()['playerId']);
    }

    /**
     * @test
     */
    public function aPlayerCanJoin(): void
    {
        $game = $this->createOpenGame();

        $game->join('playerId2');

        $domainEvents = $game->flushDomainEvents();
        $playerJoined = $domainEvents[0];

        $this->assertCount(1, $domainEvents);
        $this->assertInstanceOf(PlayerJoined::class, $playerJoined);
        $this->assertSame($game->id()->toString(), $playerJoined->aggregateId());
        $this->assertSame('playerId2', $playerJoined->payload()['playerId']);
    }

    /**
     * Returns an open game ready for testing.
     *
     * @return Game
     */
    private function createOpenGame(): Game
    {
        $game = Game::open(
            new LazyDealer(5),
            'playerId1'
        );

        $game->flushDomainEvents();

        return $game;
    }
}
