<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game\Event;

use Gaming\Common\Clock\Clock;
use Gaming\ConnectFour\Domain\Game\Event\GameDrawn;
use Gaming\ConnectFour\Domain\Game\GameId;
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
