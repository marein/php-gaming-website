<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game;

use Gaming\ConnectFour\Domain\Game\Board\Size;
use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\Event\ChatAssigned;
use Gaming\ConnectFour\Domain\Game\Event\GameAborted;
use Gaming\ConnectFour\Domain\Game\Event\GameDrawn;
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
use Gaming\ConnectFour\Domain\Game\WinningRule\CommonWinningRule;
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
        $chatAssigned = $domainEvents[0];

        $this->assertCount(1, $domainEvents);
        $this->assertInstanceOf(ChatAssigned::class, $chatAssigned);
        $this->assertSame($game->id()->toString(), $chatAssigned->aggregateId());
        $this->assertSame('chatId', $chatAssigned->payload()['chatId']);

        // If this happens twice, nothing should happen.
        $game->assignChat('anotherChatId');
        $this->assertCount(0, $game->flushDomainEvents());
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
        $gameAborted = $domainEvents[0];

        $this->assertCount(1, $domainEvents);
        $this->assertInstanceOf(GameAborted::class, $gameAborted);
        $this->assertSame($game->id()->toString(), $gameAborted->aggregateId());
        $this->assertSame('playerId1', $gameAborted->payload()['abortedPlayerId']);
        $this->assertSame('', $gameAborted->payload()['opponentPlayerId']);
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
    public function playerCanJoinAnOpenGame(): void
    {
        $game = $this->createOpenGame();

        $game->join('playerId2');

        $domainEvents = $game->flushDomainEvents();
        $playerJoined = $domainEvents[0];

        $this->assertCount(1, $domainEvents);
        $this->assertInstanceOf(PlayerJoined::class, $playerJoined);
        $this->assertSame($game->id()->toString(), $playerJoined->aggregateId());
        $this->assertSame('playerId2', $playerJoined->payload()['joinedPlayerId']);
        $this->assertSame('playerId1', $playerJoined->payload()['opponentPlayerId']);
    }

    /**
     * @test
     */
    public function playersCanMoveOnARunningGame(): void
    {
        $game = $this->createRunningGame();

        $game->move('playerId1', 1);

        $domainEvents = $game->flushDomainEvents();
        $playerMoved = $domainEvents[0];

        $this->assertCount(1, $domainEvents);
        $this->assertInstanceOf(PlayerMoved::class, $playerMoved);
        $this->assertSame($game->id()->toString(), $playerMoved->aggregateId());
        $this->assertSame(1, $playerMoved->payload()['x']);
        $this->assertSame(6, $playerMoved->payload()['y']);
        $this->assertSame(Stone::red()->color(), $playerMoved->payload()['color']);

        $game->move('playerId2', 1);

        $domainEvents = $game->flushDomainEvents();
        $playerMoved = $domainEvents[0];

        $this->assertCount(1, $domainEvents);
        $this->assertInstanceOf(PlayerMoved::class, $playerMoved);
        $this->assertSame($game->id()->toString(), $playerMoved->aggregateId());
        $this->assertSame(1, $playerMoved->payload()['x']);
        $this->assertSame(5, $playerMoved->payload()['y']);
        $this->assertSame(Stone::yellow()->color(), $playerMoved->payload()['color']);
    }

    /**
     * @test
     */
    public function playerCanWinARunningGame(): void
    {
        $game = $this->createRunningGame();

        $game->move('playerId1', 1);
        $game->move('playerId2', 2);
        $game->move('playerId1', 1);
        $game->move('playerId2', 2);
        $game->move('playerId1', 1);
        $game->move('playerId2', 2);

        // Flush, since we are only interested in the last two events.
        $game->flushDomainEvents();
        $game->move('playerId1', 1);

        $domainEvents = $game->flushDomainEvents();
        $this->assertCount(2, $domainEvents);

        $playerMoved = $domainEvents[0];
        $this->assertInstanceOf(PlayerMoved::class, $playerMoved);
        $this->assertSame($game->id()->toString(), $playerMoved->aggregateId());
        $this->assertSame(1, $playerMoved->payload()['x']);
        $this->assertSame(3, $playerMoved->payload()['y']);
        $this->assertSame(Stone::red()->color(), $playerMoved->payload()['color']);

        $gameWon = $domainEvents[1];
        $this->assertInstanceOf(GameWon::class, $gameWon);
        $this->assertSame($game->id()->toString(), $gameWon->aggregateId());
        $this->assertSame('playerId1', $gameWon->payload()['winnerPlayerId']);
    }

    /**
     * @test
     */
    public function playersCanDrawARunningGame(): void
    {
        $game = Game::open(
            Configuration::custom(
                new Size(2, 2),
                new CommonWinningRule()
            ),
            'playerId1'
        );

        $game->join('playerId2');
        $game->move('playerId1', 1);
        $game->move('playerId2', 2);
        $game->move('playerId1', 1);

        // Flush, since we are only interested in the last two events.
        $game->flushDomainEvents();
        $game->move('playerId2', 2);

        $domainEvents = $game->flushDomainEvents();
        $this->assertCount(2, $domainEvents);

        $playerMoved = $domainEvents[0];
        $this->assertInstanceOf(PlayerMoved::class, $playerMoved);
        $this->assertSame($game->id()->toString(), $playerMoved->aggregateId());
        $this->assertSame(2, $playerMoved->payload()['x']);
        $this->assertSame(1, $playerMoved->payload()['y']);
        $this->assertSame(Stone::yellow()->color(), $playerMoved->payload()['color']);

        $gameDrawn = $domainEvents[1];
        $this->assertInstanceOf(GameDrawn::class, $gameDrawn);
        $this->assertSame($game->id()->toString(), $gameDrawn->aggregateId());
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
        $gameResigned = $domainEvents[2];

        $this->assertCount(3, $domainEvents);
        $this->assertInstanceOf(GameResigned::class, $gameResigned);
        $this->assertSame($game->id()->toString(), $gameResigned->aggregateId());
        $this->assertSame('playerId1', $gameResigned->payload()['resignedPlayerId']);
        $this->assertSame('playerId2', $gameResigned->payload()['opponentPlayerId']);
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
        $gameAborted = $domainEvents[1];

        $this->assertCount(2, $domainEvents);
        $this->assertInstanceOf(GameAborted::class, $gameAborted);
        $this->assertSame($game->id()->toString(), $gameAborted->aggregateId());
        $this->assertSame('playerId1', $gameAborted->payload()['abortedPlayerId']);
        $this->assertSame('playerId2', $gameAborted->payload()['opponentPlayerId']);
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

    /**
     * Returns an open game ready for testing.
     *
     * @return Game
     */
    private function createOpenGame(): Game
    {
        $game = Game::open(
            Configuration::common(),
            'playerId1'
        );

        $game->flushDomainEvents();

        return $game;
    }

    /**
     * Returns a running game ready for testing.
     *
     * @return Game
     */
    private function createRunningGame(): Game
    {
        $game = Game::open(
            Configuration::common(),
            'playerId1'
        );

        $game->join('playerId2');

        $game->flushDomainEvents();

        return $game;
    }

    /**
     * Returns an aborted game ready for testing.
     *
     * @return Game
     */
    private function createAbortedGame(): Game
    {
        $game = $this->createOpenGame();

        $game->abort('playerId1');

        $game->flushDomainEvents();

        return $game;
    }

    /**
     * Returns a resigned game ready for testing.
     *
     * @return Game
     */
    private function createResignedGame(): Game
    {
        $game = $this->createOpenGame();

        $game->join('playerId2');
        $game->move('playerId1', 1);
        $game->move('playerId2', 1);
        $game->resign('playerId1');

        $game->flushDomainEvents();

        return $game;
    }

    /**
     * Returns a won game ready for testing.
     *
     * @return Game
     */
    private function createWonGame(): Game
    {
        $game = $this->createOpenGame();

        $game->join('playerId2');
        $game->move('playerId1', 1);
        $game->move('playerId2', 2);
        $game->move('playerId1', 1);
        $game->move('playerId2', 2);
        $game->move('playerId1', 1);
        $game->move('playerId2', 2);
        $game->move('playerId1', 1);

        $game->flushDomainEvents();

        return $game;
    }

    /**
     * Returns a drawn game ready for testing.
     *
     * @return Game
     */
    private function createDrawnGame(): Game
    {
        $game = Game::open(
            Configuration::custom(
                new Size(2, 2),
                new CommonWinningRule()
            ),
            'playerId1'
        );

        $game->join('playerId2');
        $game->move('playerId1', 1);
        $game->move('playerId2', 2);
        $game->move('playerId1', 1);
        $game->move('playerId2', 2);

        $game->flushDomainEvents();

        return $game;
    }
}
