<?php

namespace Gambling\ConnectFour\Domain\Game\Event;

use Gambling\Common\Clock\Clock;
use Gambling\ConnectFour\Domain\Game\GameId;
use PHPUnit\Framework\TestCase;

final class GameDrawnTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldBeCreatedWithItsValues(): void
    {
        Clock::instance()->freeze();

        $gameId = GameId::generate();
        $payload = [
            'gameId' => $gameId->toString()
        ];

        $gameDrawn = new GameDrawn($gameId);

        $this->assertSame('GameDrawn', $gameDrawn->name());
        $this->assertSame($gameId->toString(), $gameDrawn->aggregateId());
        $this->assertSame(Clock::instance()->now(), $gameDrawn->occurredOn());
        $this->assertSame($payload, $gameDrawn->payload());

        Clock::instance()->resume();
    }
}
