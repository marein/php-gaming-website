<?php
declare(strict_types=1);

namespace Gaming\Memory\Domain\Model\Game;

use Gaming\Memory\Domain\Model\Game\Dealer\LazyDealer;
use Gaming\Memory\Domain\Model\Game\Event\GameOpened;
use Gaming\Memory\Domain\Model\Game\Event\GameStarted;
use Gaming\Memory\Domain\Model\Game\Event\PlayerJoined;
use Gaming\Memory\Domain\Model\Game\Exception\GameNotOpenException;
use Gaming\Memory\Domain\Model\Game\Exception\PlayerNotAllowedToStartGameException;
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
    public function aPlayerCanJoinAnOpenGame(): void
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
     * @test
     */
    public function playerCanNotJoinAlreadyRunningGame(): void
    {
        $this->expectException(GameNotOpenException::class);

        $game = $this->createOpenGame();

        $game->start('playerId1');
        $game->join('playerId2');
    }

    /**
     * @test
     */
    public function theMainPlayerCanStartTheGame(): void
    {
        $game = $this->createOpenGameWithFourPlayers();

        $game->start('playerId1');

        $domainEvents = $game->flushDomainEvents();
        $gameStarted = $domainEvents[0];

        $this->assertCount(1, $domainEvents);
        $this->assertInstanceOf(GameStarted::class, $gameStarted);
        $this->assertSame($game->id()->toString(), $gameStarted->aggregateId());
        $this->assertSame($game->id()->toString(), $gameStarted->payload()['gameId']);
    }

    /**
     * @test
     */
    public function onlyTheMainPlayerCanStartTheGame(): void
    {
        $this->expectException(PlayerNotAllowedToStartGameException::class);

        $game = $this->createOpenGameWithFourPlayers();

        $game->start('playerId2');
    }

    /**
     * @test
     */
    public function canNotStartAlreadyRunningGame(): void
    {
        $this->expectException(GameNotOpenException::class);

        $game = $this->createOpenGameWithFourPlayers();

        $game->start('playerId1');
        $game->start('playerId1');
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

    /**
     * Returns an open game with four players ready for testing.
     *
     * @return Game
     */
    public function createOpenGameWithFourPlayers(): Game
    {
        $game = $this->createOpenGame();
        $game->join('playerId2');
        $game->join('playerId3');
        $game->join('playerId4');

        $game->flushDomainEvents();

        return $game;
    }
}
