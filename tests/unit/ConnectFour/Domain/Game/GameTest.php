<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game;

use Gaming\ConnectFour\Domain\Game\Board\Point;
use Gaming\ConnectFour\Domain\Game\Board\Size;
use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\Configuration;
use Gaming\ConnectFour\Domain\Game\Event\ChatAssigned;
use Gaming\ConnectFour\Domain\Game\Event\GameAborted;
use Gaming\ConnectFour\Domain\Game\Event\GameDrawn;
use Gaming\ConnectFour\Domain\Game\Event\GameOpened;
use Gaming\ConnectFour\Domain\Game\Event\GameResigned;
use Gaming\ConnectFour\Domain\Game\Event\GameWon;
use Gaming\ConnectFour\Domain\Game\Event\PlayerJoined;
use Gaming\ConnectFour\Domain\Game\Event\PlayerMoved;
use Gaming\ConnectFour\Domain\Game\Exception\GameFinishedException;
use Gaming\ConnectFour\Domain\Game\Exception\GameNotRunningException;
use Gaming\ConnectFour\Domain\Game\Exception\GameRunningException;
use Gaming\ConnectFour\Domain\Game\Exception\PlayerNotOwnerException;
use Gaming\ConnectFour\Domain\Game\Exception\PlayersNotUniqueException;
use Gaming\ConnectFour\Domain\Game\Exception\UnexpectedPlayerException;
use Gaming\ConnectFour\Domain\Game\Game;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\WinningRule\WinningRules;
use Gaming\ConnectFour\Domain\Game\WinningRule\WinningSequence;
use PHPUnit\Framework\TestCase;

/**
 * This test covers the whole Gaming\ConnectFour\Domain\Game\State namespace
 * as the game itself and its states form a conceptual unit.
 */
class GameTest extends TestCase
{
    /**
     * @test
     */
    public function aChatCanBeAssigned(): void
    {
        $game = $this->createOpenGame();

        $game->assignChat('chatId');

        $domainEvents = $game->flushDomainEvents();
        self::assertCount(1, $domainEvents);

        assert($domainEvents[0] instanceof ChatAssigned);
        self::assertEquals($game->id()->toString(), $domainEvents[0]->aggregateId());
        self::assertEquals('chatId', $domainEvents[0]->chatId());

        // If this happens twice, nothing should happen.
        $game->assignChat('anotherChatId');
        self::assertCount(0, $game->flushDomainEvents());
    }

    /**
     * @test
     */
    public function playerCanNotResignAnOpenGame(): void
    {
        $this->expectException(GameNotRunningException::class);

        $this
            ->createOpenGame()
            ->resign('playerId1');
    }

    /**
     * @test
     */
    public function playerCanNotMoveOnAnOpenGame(): void
    {
        $this->expectException(GameNotRunningException::class);

        $this
            ->createOpenGame()
            ->move('playerId1', 1);
    }

    /**
     * @test
     */
    public function playerCanNotAbortAnOpenGameIfPlayerIsNotOwner(): void
    {
        $this->expectException(PlayerNotOwnerException::class);

        $this
            ->createOpenGame()
            ->abort('playerId3');
    }

    /**
     * @test
     */
    public function playerCanAbortAnOpenGame(): void
    {
        $game = $this->createOpenGame();

        $game->abort('playerId1');

        $domainEvents = $game->flushDomainEvents();
        self::assertCount(1, $domainEvents);

        assert($domainEvents[0] instanceof GameAborted);
        self::assertEquals($game->id()->toString(), $domainEvents[0]->aggregateId());
        self::assertEquals('playerId1', $domainEvents[0]->abortedPlayerId());
        self::assertEquals('', $domainEvents[0]->opponentPlayerId());
    }

    /**
     * @test
     */
    public function samePlayerCanNotJoinAnOpenGame(): void
    {
        $this->expectException(PlayersNotUniqueException::class);

        $this
            ->createOpenGame()
            ->join('playerId1');
    }

    /**
     * @test
     */
    public function playerCanNotMoveTwiceInARunningGame(): void
    {
        $this->expectException(UnexpectedPlayerException::class);

        $game = $this->createRunningGame();

        $game->move('playerId1', 1);
        $game->move('playerId1', 2);
    }

    /**
     * @test
     */
    public function playerCanResignARunningGameAfterTheSecondMove(): void
    {
        $game = $this->createRunningGame();

        $game->move('playerId1', 1);
        $game->move('playerId2', 2);
        $game->resign('playerId1');

        $domainEvents = $game->flushDomainEvents();
        self::assertCount(3, $domainEvents);

        assert($domainEvents[2] instanceof GameResigned);
        self::assertEquals($game->id()->toString(), $domainEvents[2]->aggregateId());
        self::assertEquals('playerId1', $domainEvents[2]->resignedPlayerId());
        self::assertEquals('playerId2', $domainEvents[2]->opponentPlayerId());
    }

    /**
     * @test
     */
    public function playerCanNotResignARunningGameBeforeTheSecondMove(): void
    {
        $this->expectException(GameNotRunningException::class);

        $game = $this->createRunningGame();

        $game->move('playerId1', 1);
        $game->resign('playerId1');
    }

    /**
     * @test
     */
    public function playerCanNotResignARunningGameIfPlayerIsNotOwner(): void
    {
        $this->expectException(PlayerNotOwnerException::class);

        $game = $this->createRunningGame();

        $game->move('playerId1', 1);
        $game->move('playerId2', 2);
        $game->resign('playerId3');
    }

    /**
     * @test
     */
    public function playerCanAbortARunningGameBeforeTheSecondMove(): void
    {
        $game = $this->createRunningGame();

        $game->move('playerId1', 1);
        $game->abort('playerId1');

        $domainEvents = $game->flushDomainEvents();
        self::assertCount(2, $domainEvents);

        assert($domainEvents[1] instanceof GameAborted);
        self::assertEquals($game->id()->toString(), $domainEvents[1]->aggregateId());
        self::assertEquals('playerId1', $domainEvents[1]->abortedPlayerId());
        self::assertEquals('playerId2', $domainEvents[1]->opponentPlayerId());
    }

    /**
     * @test
     */
    public function playerCanNotAbortARunningGameAfterTheSecondMove(): void
    {
        $this->expectException(GameRunningException::class);

        $game = $this->createRunningGame();

        $game->move('playerId1', 1);
        $game->move('playerId2', 2);
        $game->abort('playerId1');
    }

    /**
     * @test
     */
    public function playerCanNotAbortARunningGameIfPlayerIsNotOwner(): void
    {
        $this->expectException(PlayerNotOwnerException::class);

        $game = $this->createRunningGame();

        $game->abort('playerId3');
    }

    /**
     * @test
     */
    public function playerCanNotJoinARunningGame(): void
    {
        $this->expectException(GameRunningException::class);

        $this
            ->createRunningGame()
            ->join('playerId3');
    }

    /**
     * @test
     */
    public function playerCanNotResignAnAbortedGame(): void
    {
        $this->expectException(GameFinishedException::class);

        $this
            ->createAbortedGame()
            ->resign('playerId1');
    }

    /**
     * @test
     */
    public function playerCanNotJoinAnAbortedGame(): void
    {
        $this->expectException(GameFinishedException::class);

        $this
            ->createAbortedGame()
            ->join('playerId2');
    }

    /**
     * @test
     */
    public function playerCanNotMoveOnAnAbortedGame(): void
    {
        $this->expectException(GameFinishedException::class);

        $this
            ->createAbortedGame()
            ->move('playerId1', 1);
    }

    /**
     * @test
     */
    public function playerCanNotAbortAnAbortedGame(): void
    {
        $this->expectException(GameFinishedException::class);

        $this
            ->createAbortedGame()
            ->abort('playerId1');
    }

    /**
     * @test
     */
    public function playerCanNotResignAResignedGame(): void
    {
        $this->expectException(GameFinishedException::class);

        $this
            ->createResignedGame()
            ->resign('playerId1');
    }

    /**
     * @test
     */
    public function playerCanNotJoinAResignedGame(): void
    {
        $this->expectException(GameFinishedException::class);

        $this
            ->createResignedGame()
            ->join('playerId2');
    }

    /**
     * @test
     */
    public function playerCanNotMoveOnAResignedGame(): void
    {
        $this->expectException(GameFinishedException::class);

        $this
            ->createResignedGame()
            ->move('playerId1', 1);
    }

    /**
     * @test
     */
    public function playerCanNotAbortAResignedGame(): void
    {
        $this->expectException(GameFinishedException::class);

        $this
            ->createResignedGame()
            ->abort('playerId1');
    }

    /**
     * @test
     */
    public function playerCanNotResignAWonGame(): void
    {
        $this->expectException(GameFinishedException::class);

        $this
            ->createWonGame()
            ->resign('playerId1');
    }

    /**
     * @test
     */
    public function playerCanNotJoinAWonGame(): void
    {
        $this->expectException(GameFinishedException::class);

        $this
            ->createWonGame()
            ->join('playerId2');
    }

    /**
     * @test
     */
    public function playerCanNotMoveOnAWonGame(): void
    {
        $this->expectException(GameFinishedException::class);

        $this
            ->createWonGame()
            ->move('playerId1', 1);
    }

    /**
     * @test
     */
    public function playerCanNotAbortAWonGame(): void
    {
        $this->expectException(GameFinishedException::class);

        $this
            ->createWonGame()
            ->abort('playerId1');
    }

    /**
     * @test
     */
    public function playerCanNotResignADrawnGame(): void
    {
        $this->expectException(GameFinishedException::class);

        $this
            ->createDrawnGame()
            ->resign('playerId1');
    }

    /**
     * @test
     */
    public function playerCanNotJoinADrawnGame(): void
    {
        $this->expectException(GameFinishedException::class);

        $this
            ->createDrawnGame()
            ->join('playerId2');
    }

    /**
     * @test
     */
    public function playerCanNotMoveOnADrawnGame(): void
    {
        $this->expectException(GameFinishedException::class);

        $this
            ->createDrawnGame()
            ->move('playerId1', 1);
    }

    /**
     * @test
     */
    public function playerCanNotAbortADrawnGame(): void
    {
        $this->expectException(GameFinishedException::class);

        $this
            ->createDrawnGame()
            ->abort('playerId1');
    }

    private function createOpenGame(): Game
    {
        $game = Game::open(
            GameId::generate(),
            Configuration::common(),
            'playerId1'
        );

        $domainEvents = $game->flushDomainEvents();
        self::assertCount(1, $domainEvents);

        assert($domainEvents[0] instanceof GameOpened);
        self::assertEquals($game->id()->toString(), $domainEvents[0]->aggregateId());
        self::assertEquals('playerId1', $domainEvents[0]->playerId());
        self::assertEquals(7, $domainEvents[0]->width());
        self::assertEquals(6, $domainEvents[0]->height());
        self::assertEquals(1, $domainEvents[0]->preferredStone);

        return $game;
    }

    private function createRunningGame(): Game
    {
        $game = $this->createOpenGame();

        $game->join('playerId2');

        $domainEvents = $game->flushDomainEvents();
        self::assertCount(1, $domainEvents);

        assert($domainEvents[0] instanceof PlayerJoined);
        self::assertEquals($game->id()->toString(), $domainEvents[0]->aggregateId());
        self::assertEquals('playerId1', $domainEvents[0]->opponentPlayerId());
        self::assertEquals('playerId2', $domainEvents[0]->joinedPlayerId());
        self::assertEquals('playerId1', $domainEvents[0]->redPlayerId);
        self::assertEquals('playerId2', $domainEvents[0]->yellowPlayerId);

        return $game;
    }

    private function createAbortedGame(): Game
    {
        $game = $this->createOpenGame();

        $game->abort('playerId1');

        $domainEvents = $game->flushDomainEvents();
        self::assertCount(1, $domainEvents);

        assert($domainEvents[0] instanceof GameAborted);
        self::assertEquals($game->id()->toString(), $domainEvents[0]->aggregateId());
        self::assertEquals('playerId1', $domainEvents[0]->abortedPlayerId());
        self::assertEquals('', $domainEvents[0]->opponentPlayerId());

        return $game;
    }

    private function createResignedGame(): Game
    {
        $game = $this->createRunningGame();

        $game->move('playerId1', 1);
        $game->move('playerId2', 1);
        $game->resign('playerId1');

        $domainEvents = $game->flushDomainEvents();
        self::assertCount(3, $domainEvents);

        $this->assertPlayerMoved($domainEvents[0], $game->id(), 1, 6, Stone::Red, 'playerId1', 'playerId2');
        $this->assertPlayerMoved($domainEvents[1], $game->id(), 1, 5, Stone::Yellow, 'playerId2', 'playerId1');

        assert($domainEvents[2] instanceof GameResigned);
        self::assertEquals($game->id()->toString(), $domainEvents[2]->aggregateId());

        return $game;
    }

    private function createWonGame(): Game
    {
        $game = $this->createRunningGame();

        $game->move('playerId1', 1);
        $game->move('playerId2', 2);
        $game->move('playerId1', 1);
        $game->move('playerId2', 2);
        $game->move('playerId1', 1);
        $game->move('playerId2', 2);
        $game->move('playerId1', 1);

        $domainEvents = $game->flushDomainEvents();
        self::assertCount(8, $domainEvents);

        $this->assertPlayerMoved($domainEvents[0], $game->id(), 1, 6, Stone::Red, 'playerId1', 'playerId2');
        $this->assertPlayerMoved($domainEvents[1], $game->id(), 2, 6, Stone::Yellow, 'playerId2', 'playerId1');
        $this->assertPlayerMoved($domainEvents[2], $game->id(), 1, 5, Stone::Red, 'playerId1', 'playerId2');
        $this->assertPlayerMoved($domainEvents[3], $game->id(), 2, 5, Stone::Yellow, 'playerId2', 'playerId1');
        $this->assertPlayerMoved($domainEvents[4], $game->id(), 1, 4, Stone::Red, 'playerId1', 'playerId2');
        $this->assertPlayerMoved($domainEvents[5], $game->id(), 2, 4, Stone::Yellow, 'playerId2', 'playerId1');
        $this->assertPlayerMoved($domainEvents[6], $game->id(), 1, 3, Stone::Red, 'playerId1', 'playerId2');

        assert($domainEvents[7] instanceof GameWon);
        self::assertEquals($game->id()->toString(), $domainEvents[7]->aggregateId());
        self::assertEquals('playerId1', $domainEvents[7]->winnerPlayerId());
        self::assertEquals('playerId1', $domainEvents[7]->winnerId);
        self::assertEquals('playerId2', $domainEvents[7]->loserId);
        self::assertEquals(
            [new WinningSequence('vertical', [new Point(1, 3), new Point(1, 4), new Point(1, 5), new Point(1, 6)])],
            $domainEvents[7]->winningSequences()
        );

        return $game;
    }

    private function createDrawnGame(): Game
    {
        $game = Game::open(
            GameId::generate(),
            new Configuration(
                new Size(2, 2),
                WinningRules::standard(),
                Stone::Red
            ),
            'playerId1'
        );

        $game->join('playerId2');
        $game->move('playerId1', 1);
        $game->move('playerId2', 2);
        $game->move('playerId1', 1);
        $game->move('playerId2', 2);

        $domainEvents = $game->flushDomainEvents();
        self::assertCount(7, $domainEvents);

        assert($domainEvents[0] instanceof GameOpened);
        self::assertEquals($game->id()->toString(), $domainEvents[0]->aggregateId());
        self::assertEquals('playerId1', $domainEvents[0]->playerId());
        self::assertEquals(2, $domainEvents[0]->width());
        self::assertEquals(2, $domainEvents[0]->height());
        self::assertEquals(1, $domainEvents[0]->preferredStone);

        assert($domainEvents[1] instanceof PlayerJoined);
        self::assertEquals($game->id()->toString(), $domainEvents[1]->aggregateId());
        self::assertEquals('playerId2', $domainEvents[1]->joinedPlayerId());
        self::assertEquals('playerId1', $domainEvents[1]->opponentPlayerId());
        self::assertEquals('playerId1', $domainEvents[1]->redPlayerId);
        self::assertEquals('playerId2', $domainEvents[1]->yellowPlayerId);

        $this->assertPlayerMoved($domainEvents[2], $game->id(), 1, 2, Stone::Red, 'playerId1', 'playerId2');
        $this->assertPlayerMoved($domainEvents[3], $game->id(), 2, 2, Stone::Yellow, 'playerId2', 'playerId1');
        $this->assertPlayerMoved($domainEvents[4], $game->id(), 1, 1, Stone::Red, 'playerId1', 'playerId2');
        $this->assertPlayerMoved($domainEvents[5], $game->id(), 2, 1, Stone::Yellow, 'playerId2', 'playerId1');

        assert($domainEvents[6] instanceof GameDrawn);
        self::assertEquals($game->id()->toString(), $domainEvents[6]->aggregateId());
        self::assertEquals(['playerId2', 'playerId1'], $domainEvents[6]->playerIds);

        return $game;
    }

    private function assertPlayerMoved(
        object $playerMoved,
        GameId $gameId,
        int $x,
        int $y,
        Stone $stone,
        string $playerId,
        string $nextPlayerId
    ): void {
        assert($playerMoved instanceof PlayerMoved);
        self::assertEquals($gameId->toString(), $playerMoved->aggregateId());
        self::assertEquals($x, $playerMoved->x());
        self::assertEquals($y, $playerMoved->y());
        self::assertEquals($stone->value, $playerMoved->color());
        self::assertEquals($playerId, $playerMoved->playerId);
        self::assertEquals($nextPlayerId, $playerMoved->nextPlayerId);
    }
}
