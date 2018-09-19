<?php
declare(strict_types=1);

namespace Gambling\ConnectFour\Domain\Game\Event;

use Gambling\Common\Clock\Clock;
use Gambling\ConnectFour\Domain\Game\Board\Stone;
use Gambling\ConnectFour\Domain\Game\GameId;
use Gambling\ConnectFour\Domain\Game\Player;
use PHPUnit\Framework\TestCase;

final class GameAbortedTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldBeCreatedWithOpponentPlayer(): void
    {
        Clock::instance()->freeze();

        $gameId = GameId::generate();
        $abortedPlayerId = 'abortedPlayerId';
        $opponentPlayerId = 'opponentPlayerId';
        $payload = [
            'gameId'           => $gameId->toString(),
            'abortedPlayerId'  => $abortedPlayerId,
            'opponentPlayerId' => $opponentPlayerId
        ];

        $gameAborted = new GameAborted(
            $gameId,
            new Player($abortedPlayerId, Stone::red()),
            new Player($opponentPlayerId, Stone::yellow())
        );

        $this->assertSame('GameAborted', $gameAborted->name());
        $this->assertSame($gameId->toString(), $gameAborted->aggregateId());
        $this->assertSame(Clock::instance()->now(), $gameAborted->occurredOn());
        $this->assertSame($payload, $gameAborted->payload());

        Clock::instance()->resume();
    }

    /**
     * @test
     */
    public function itShouldBeCreatedWithoutOpponentPlayer(): void
    {
        Clock::instance()->freeze();

        $gameId = GameId::generate();
        $abortedPlayerId = 'abortedPlayerId';
        $opponentPlayerId = '';
        $payload = [
            'gameId'           => $gameId->toString(),
            'abortedPlayerId'  => $abortedPlayerId,
            'opponentPlayerId' => $opponentPlayerId
        ];

        $gameAborted = new GameAborted(
            $gameId,
            new Player($abortedPlayerId, Stone::red())
        );

        $this->assertSame('GameAborted', $gameAborted->name());
        $this->assertSame($gameId->toString(), $gameAborted->aggregateId());
        $this->assertSame(Clock::instance()->now(), $gameAborted->occurredOn());
        $this->assertSame($payload, $gameAborted->payload());

        Clock::instance()->resume();
    }
}
