<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Memory\Domain\Model\Game;

use Gaming\Memory\Domain\Model\Game\Dealer\LazyDealer;
use Gaming\Memory\Domain\Model\Game\Event\GameClosed;
use Gaming\Memory\Domain\Model\Game\Event\GameOpened;
use Gaming\Memory\Domain\Model\Game\Event\GameStarted;
use Gaming\Memory\Domain\Model\Game\Event\PlayerJoined;
use Gaming\Memory\Domain\Model\Game\Event\PlayerLeft;
use Gaming\Memory\Domain\Model\Game\Exception\GameNotOpenException;
use Gaming\Memory\Domain\Model\Game\Exception\PlayerNotAllowedToStartGameException;
use Gaming\Memory\Domain\Model\Game\Game;
use PHPUnit\Framework\TestCase;

class GameTest extends TestCase
{
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
    public function aPlayerCanLeaveAnOpenGame(): void
    {
        $game = $this->createOpenGameWithFourPlayers();

        $game->leave('playerId1');

        $domainEvents = $game->flushDomainEvents();
        self::assertCount(1, $domainEvents);

        assert($domainEvents[0] instanceof PlayerLeft);
        self::assertEquals($game->id()->toString(), $domainEvents[0]->aggregateId());
        self::assertEquals('playerId1', $domainEvents[0]->playerId());
    }

    /**
     * @test
     */
    public function ifTheLastPlayerLeavesTheGameTheGameGetsClosed(): void
    {
        $game = $this->createOpenGame();

        $game->leave('playerId1');

        $domainEvents = $game->flushDomainEvents();
        self::assertCount(2, $domainEvents);

        assert($domainEvents[0] instanceof PlayerLeft);
        self::assertEquals($game->id()->toString(), $domainEvents[0]->aggregateId());
        self::assertEquals('playerId1', $domainEvents[0]->playerId());

        assert($domainEvents[1] instanceof GameClosed);
        self::assertEquals($game->id()->toString(), $domainEvents[1]->aggregateId());
    }

    /**
     * @test
     */
    public function playerCanNotLeaveAlreadyRunningGame(): void
    {
        $this->expectException(GameNotOpenException::class);

        $game = $this->createOpenGame();

        $game->start('playerId1');
        $game->leave('playerId1');
    }

    /**
     * @test
     */
    public function theMainPlayerCanStartTheGame(): void
    {
        $game = $this->createOpenGameWithFourPlayers();

        $game->start('playerId1');

        $domainEvents = $game->flushDomainEvents();
        self::assertCount(1, $domainEvents);

        assert($domainEvents[0] instanceof GameStarted);
        self::assertEquals($game->id()->toString(), $domainEvents[0]->aggregateId());
        self::assertEquals(['playerId1', 'playerId2', 'playerId3', 'playerId4'], $domainEvents[0]->playerIds());
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

    private function createOpenGame(): Game
    {
        $game = Game::open(
            new LazyDealer(5),
            'playerId1'
        );

        $domainEvents = $game->flushDomainEvents();
        self::assertCount(1, $domainEvents);

        assert($domainEvents[0] instanceof GameOpened);
        self::assertEquals($game->id()->toString(), $domainEvents[0]->aggregateId());
        self::assertEquals(10, $domainEvents[0]->numberOfCards());
        self::assertEquals('playerId1', $domainEvents[0]->playerId());

        return $game;
    }

    public function createOpenGameWithFourPlayers(): Game
    {
        $game = $this->createOpenGame();
        $game->join('playerId2');
        $game->join('playerId3');
        $game->join('playerId4');

        $domainEvents = $game->flushDomainEvents();
        self::assertCount(3, $domainEvents);

        assert($domainEvents[0] instanceof PlayerJoined);
        self::assertEquals($game->id()->toString(), $domainEvents[0]->aggregateId());
        self::assertEquals('playerId2', $domainEvents[0]->playerId());

        assert($domainEvents[1] instanceof PlayerJoined);
        self::assertEquals($game->id()->toString(), $domainEvents[1]->aggregateId());
        self::assertEquals('playerId3', $domainEvents[1]->playerId());

        assert($domainEvents[2] instanceof PlayerJoined);
        self::assertEquals($game->id()->toString(), $domainEvents[2]->aggregateId());
        self::assertEquals('playerId4', $domainEvents[2]->playerId());

        return $game;
    }
}
