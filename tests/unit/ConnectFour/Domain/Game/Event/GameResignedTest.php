<?php
declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game\Event;

use Gaming\Common\Clock\Clock;
use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\Event\GameResigned;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\Player;
use PHPUnit\Framework\TestCase;

final class GameResignedTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldBeCreatedWithItsValues(): void
    {
        Clock::instance()->freeze();

        $gameId = GameId::generate();
        $resignedPlayerId = 'resignedPlayerId';
        $opponentPlayerId = 'opponentPlayerId';
        $payload = [
            'gameId'           => $gameId->toString(),
            'resignedPlayerId' => $resignedPlayerId,
            'opponentPlayerId' => $opponentPlayerId
        ];

        $gameResigned = new GameResigned(
            $gameId,
            new Player($resignedPlayerId, Stone::red()),
            new Player($opponentPlayerId, Stone::yellow())
        );

        $this->assertSame('GameResigned', $gameResigned->name());
        $this->assertSame($gameId->toString(), $gameResigned->aggregateId());
        $this->assertSame(Clock::instance()->now(), $gameResigned->occurredOn());
        $this->assertSame($payload, $gameResigned->payload());

        Clock::instance()->resume();
    }
}
