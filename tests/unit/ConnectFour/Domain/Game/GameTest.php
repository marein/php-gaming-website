<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game;

use Closure;
use Codeception\Attribute\DataProvider;
use DateTimeImmutable;
use Gaming\Common\Domain\Test\DomainAssert;
use Gaming\Common\Timer\MoveTimer;
use Gaming\ConnectFour\Domain\Game\Board\Point;
use Gaming\ConnectFour\Domain\Game\Board\Size;
use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\Configuration;
use Gaming\ConnectFour\Domain\Game\Event\ChatAssigned;
use Gaming\ConnectFour\Domain\Game\Event\GameAborted;
use Gaming\ConnectFour\Domain\Game\Event\GameDrawn;
use Gaming\ConnectFour\Domain\Game\Event\GameOpened;
use Gaming\ConnectFour\Domain\Game\Event\GameResigned;
use Gaming\ConnectFour\Domain\Game\Event\GameTimedOut;
use Gaming\ConnectFour\Domain\Game\Event\GameWon;
use Gaming\ConnectFour\Domain\Game\Event\PlayerJoined;
use Gaming\ConnectFour\Domain\Game\Event\PlayerMoved;
use Gaming\ConnectFour\Domain\Game\Exception\GameException;
use Gaming\ConnectFour\Domain\Game\Exception\GameFinishedException;
use Gaming\ConnectFour\Domain\Game\Game;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\WinningRule\WinningRules;
use Gaming\ConnectFour\Domain\Game\WinningRule\WinningSequence;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * This test covers the whole Gaming\ConnectFour\Domain\Game\State namespace
 * as the game itself and its states form a conceptual unit.
 */
class GameTest extends TestCase
{
    #[Test]
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

    #[Test]
    public function playerCanNotAbortAnOpenGameIfPlayerIsNotOwner(): void
    {
        $game = $this->createOpenGame();

        DomainAssert::expectViolation(
            fn() => $game->abort('playerId3'),
            GameException::class,
            'player_not_owner'
        );
    }

    #[Test]
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

    #[Test]
    public function samePlayerCanNotJoinAnOpenGame(): void
    {
        $game = $this->createOpenGame();

        DomainAssert::expectViolation(
            fn() => $game->join('playerId1'),
            GameException::class,
            'players_not_unique'
        );
    }

    #[Test]
    public function playerCanNotMoveTwiceInARunningGame(): void
    {
        $game = $this->createRunningGame();

        $game->move('playerId1', 1);
        DomainAssert::expectViolation(
            fn() => $game->move('playerId1', 2),
            GameException::class,
            'unexpected_player'
        );
    }

    #[Test]
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

    #[Test]
    public function playerCanNotResignARunningGameBeforeTheSecondMove(): void
    {
        $game = $this->createRunningGame();

        $game->move('playerId1', 1);
        DomainAssert::expectViolation(
            fn() => $game->resign('playerId1'),
            GameException::class,
            'game_not_running'
        );
    }

    #[Test]
    public function playerCanNotResignARunningGameIfPlayerIsNotOwner(): void
    {
        $game = $this->createRunningGame();

        $game->move('playerId1', 1);
        $game->move('playerId2', 2);
        DomainAssert::expectViolation(
            fn() => $game->resign('playerId3'),
            GameException::class,
            'player_not_owner'
        );
    }

    #[Test]
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

    #[Test]
    public function playerCanNotAbortARunningGameAfterTheSecondMove(): void
    {
        $game = $this->createRunningGame();

        $game->move('playerId1', 1);
        $game->move('playerId2', 2);
        DomainAssert::expectViolation(
            fn() => $game->abort('playerId1'),
            GameException::class,
            'game_already_running'
        );
    }

    #[Test]
    public function playerCanNotAbortARunningGameIfPlayerIsNotOwner(): void
    {
        $game = $this->createRunningGame();

        DomainAssert::expectViolation(
            fn() => $game->abort('playerId3'),
            GameException::class,
            'player_not_owner'
        );
    }

    #[Test]
    public function timeoutHappensOnMove(): Game
    {
        $game = $this->createRunningGame();

        $game->move('playerId1', 1);
        $game->move('playerId2', 2);
        $game->move('playerId1', 1, new DateTimeImmutable('+1 year'));

        $domainEvents = $game->flushDomainEvents();
        self::assertCount(3, $domainEvents);

        assert($domainEvents[2] instanceof GameTimedOut);
        self::assertEquals($game->id()->toString(), $domainEvents[2]->gameId);
        self::assertEquals('playerId1', $domainEvents[2]->timedOutPlayerId);
        self::assertEquals('playerId2', $domainEvents[2]->opponentPlayerId);

        return $game;
    }

    #[Test]
    public function timeoutAbortsInTheFirstTwoMoves(): void
    {
        // First move
        $game = $this->createRunningGame();

        $game->timeout(new DateTimeImmutable('+1 year'));

        $domainEvents = $game->flushDomainEvents();
        self::assertCount(1, $domainEvents);

        assert($domainEvents[0] instanceof GameAborted);
        self::assertEquals($game->id()->toString(), $domainEvents[0]->aggregateId());
        self::assertEquals('playerId1', $domainEvents[0]->abortedPlayerId());
        self::assertEquals('playerId2', $domainEvents[0]->opponentPlayerId());

        // Second move
        $game = $this->createRunningGame();

        $game->move('playerId1', 1);
        $game->timeout(new DateTimeImmutable('+1 year'));

        $domainEvents = $game->flushDomainEvents();
        self::assertCount(2, $domainEvents);

        assert($domainEvents[1] instanceof GameAborted);
        self::assertEquals($game->id()->toString(), $domainEvents[1]->aggregateId());
        self::assertEquals('playerId1', $domainEvents[1]->opponentPlayerId());
        self::assertEquals('playerId2', $domainEvents[1]->abortedPlayerId());
    }

    #[Test]
    #[DataProvider('unallowedTransitionsProvider')]
    public function unallowedTransitions(
        Closure $createGame,
        Closure $transition,
        string $expectedIdentifier,
        string $expectedException
    ): void {
        $game = $createGame();

        DomainAssert::expectViolation(
            fn() => $transition($game),
            $expectedException,
            $expectedIdentifier
        );
    }

    public function unallowedTransitionsProvider(): iterable
    {
        yield [
            fn() => $this->createOpenGame(),
            static fn(Game $game) => $game->resign('playerId1'),
            'game_not_running',
            GameException::class
        ];
        yield [
            fn() => $this->createOpenGame(),
            static fn(Game $game) => $game->move('playerId1', 1),
            'game_not_running',
            GameException::class
        ];
        yield [
            fn() => $this->createOpenGame(),
            static fn(Game $game) => $game->timeout(),
            'game_not_running',
            GameException::class
        ];
        yield [
            fn() => $this->createRunningGame(),
            static fn(Game $game) => $game->join('playerId2'),
            'game_already_running',
            GameException::class
        ];
        yield [
            fn() => $this->createRunningGame(),
            static fn(Game $game) => $game->timeout(),
            'no_timeout',
            GameException::class
        ];
        yield [
            fn() => $this->createWonGame(),
            static fn(Game $game) => $game->join('playerId2'),
            'game_already_finished',
            GameFinishedException::class
        ];
        yield [
            fn() => $this->createWonGame(),
            static fn(Game $game) => $game->move('playerId1', 1),
            'game_already_finished',
            GameFinishedException::class
        ];
        yield [
            fn() => $this->createWonGame(),
            static fn(Game $game) => $game->resign('playerId1'),
            'game_already_finished',
            GameFinishedException::class
        ];
        yield [
            fn() => $this->createWonGame(),
            static fn(Game $game) => $game->abort('playerId1'),
            'game_already_finished',
            GameFinishedException::class
        ];
        yield [
            fn() => $this->createWonGame(),
            static fn(Game $game) => $game->timeout(),
            'game_already_finished',
            GameFinishedException::class
        ];
        yield [
            fn() => $this->createResignedGame(),
            static fn(Game $game) => $game->join('playerId2'),
            'game_already_finished',
            GameFinishedException::class
        ];
        yield [
            fn() => $this->createResignedGame(),
            static fn(Game $game) => $game->move('playerId1', 1),
            'game_already_finished',
            GameFinishedException::class
        ];
        yield [
            fn() => $this->createResignedGame(),
            static fn(Game $game) => $game->resign('playerId1'),
            'game_already_finished',
            GameFinishedException::class
        ];
        yield [
            fn() => $this->createResignedGame(),
            static fn(Game $game) => $game->abort('playerId1'),
            'game_already_finished',
            GameFinishedException::class
        ];
        yield [
            fn() => $this->createResignedGame(),
            static fn(Game $game) => $game->timeout(),
            'game_already_finished',
            GameFinishedException::class
        ];
        yield [
            fn() => $this->createDrawnGame(),
            static fn(Game $game) => $game->join('playerId2'),
            'game_already_finished',
            GameFinishedException::class
        ];
        yield [
            fn() => $this->createDrawnGame(),
            static fn(Game $game) => $game->move('playerId1', 1),
            'game_already_finished',
            GameFinishedException::class
        ];
        yield [
            fn() => $this->createDrawnGame(),
            static fn(Game $game) => $game->resign('playerId1'),
            'game_already_finished',
            GameFinishedException::class
        ];
        yield [
            fn() => $this->createDrawnGame(),
            static fn(Game $game) => $game->abort('playerId1'),
            'game_already_finished',
            GameFinishedException::class
        ];
        yield [
            fn() => $this->createDrawnGame(),
            static fn(Game $game) => $game->timeout(),
            'game_already_finished',
            GameFinishedException::class
        ];
        yield [
            fn() => $this->createAbortedGame(),
            static fn(Game $game) => $game->join('playerId2'),
            'game_already_finished',
            GameFinishedException::class
        ];
        yield [
            fn() => $this->createAbortedGame(),
            static fn(Game $game) => $game->move('playerId1', 1),
            'game_already_finished',
            GameFinishedException::class
        ];
        yield [
            fn() => $this->createAbortedGame(),
            static fn(Game $game) => $game->resign('playerId1'),
            'game_already_finished',
            GameFinishedException::class
        ];
        yield [
            fn() => $this->createAbortedGame(),
            static fn(Game $game) => $game->abort('playerId1'),
            'game_already_finished',
            GameFinishedException::class
        ];
        yield [
            fn() => $this->createAbortedGame(),
            static fn(Game $game) => $game->timeout(),
            'game_already_finished',
            GameFinishedException::class
        ];
        yield [
            fn() => $this->createTimedOutGame(),
            static fn(Game $game) => $game->join('playerId2'),
            'game_already_finished',
            GameFinishedException::class
        ];
        yield [
            fn() => $this->createTimedOutGame(),
            static fn(Game $game) => $game->move('playerId1', 1),
            'game_already_finished',
            GameFinishedException::class
        ];
        yield [
            fn() => $this->createTimedOutGame(),
            static fn(Game $game) => $game->resign('playerId1'),
            'game_already_finished',
            GameFinishedException::class
        ];
        yield [
            fn() => $this->createTimedOutGame(),
            static fn(Game $game) => $game->abort('playerId1'),
            'game_already_finished',
            GameFinishedException::class
        ];
        yield [
            fn() => $this->createTimedOutGame(),
            static fn(Game $game) => $game->timeout(),
            'game_already_finished',
            GameFinishedException::class
        ];
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
        self::assertEquals('game:60000:0', $domainEvents[0]->timer);

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
                Stone::Red,
                MoveTimer::set(15000)
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
        self::assertEquals('move:15000', $domainEvents[0]->timer);

        assert($domainEvents[1] instanceof PlayerJoined);
        self::assertEquals($game->id()->toString(), $domainEvents[1]->aggregateId());
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

    private function createTimedOutGame(): Game
    {
        $game = $this->createRunningGame();

        $game->move('playerId1', 1);
        $game->move('playerId2', 2);
        $game->timeout(new DateTimeImmutable('+1 year'));

        $domainEvents = $game->flushDomainEvents();
        self::assertCount(3, $domainEvents);

        $this->assertPlayerMoved($domainEvents[0], $game->id(), 1, 6, Stone::Red, 'playerId1', 'playerId2');
        $this->assertPlayerMoved($domainEvents[1], $game->id(), 2, 6, Stone::Yellow, 'playerId2', 'playerId1');

        assert($domainEvents[2] instanceof GameTimedOut);
        self::assertEquals($game->id()->toString(), $domainEvents[2]->gameId);
        self::assertEquals('playerId1', $domainEvents[2]->timedOutPlayerId);
        self::assertEquals('playerId2', $domainEvents[2]->opponentPlayerId);

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
