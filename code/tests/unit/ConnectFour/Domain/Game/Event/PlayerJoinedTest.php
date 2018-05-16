<?php

namespace Gambling\ConnectFour\Domain\Game\Event;

use Gambling\Common\Clock\Clock;
use Gambling\ConnectFour\Domain\Game\Board\Stone;
use Gambling\ConnectFour\Domain\Game\GameId;
use Gambling\ConnectFour\Domain\Game\Player;
use PHPUnit\Framework\TestCase;

final class PlayerJoinedTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldBeCreatedWithItsValues(): void
    {
        Clock::instance()->freeze();

        $gameId = GameId::generate();
        $joinedPlayerId = 'joinedPlayerId';
        $opponentPlayerId = 'opponentPlayerId';
        $payload = [
            'gameId'           => $gameId->toString(),
            'opponentPlayerId' => $opponentPlayerId,
            'joinedPlayerId'   => $joinedPlayerId
        ];

        $playerJoined = new PlayerJoined(
            $gameId,
            new Player($joinedPlayerId, Stone::yellow()),
            new Player($opponentPlayerId, Stone::red())
        );

        $this->assertSame('PlayerJoined', $playerJoined->name());
        $this->assertSame($gameId->toString(), $playerJoined->aggregateId());
        $this->assertSame(Clock::instance()->now(), $playerJoined->occurredOn());
        $this->assertSame($payload, $playerJoined->payload());

        Clock::instance()->resume();
    }
}
