<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game\Event;

use Gaming\Common\Clock\Clock;
use Gaming\ConnectFour\Domain\Game\Board\Point;
use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\Event\PlayerMoved;
use Gaming\ConnectFour\Domain\Game\GameId;
use PHPUnit\Framework\TestCase;

final class PlayerMovedTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldBeCreatedWithItsValues(): void
    {
        Clock::instance()->freeze();

        $gameId = GameId::generate();
        $x = 4;
        $y = 2;
        $color = Stone::red()->color();
        $payload = [
            'gameId' => $gameId->toString(),
            'x' => $x,
            'y' => $y,
            'color' => $color
        ];

        $playerMoved = new PlayerMoved(
            $gameId,
            new Point($x, $y),
            Stone::red()
        );

        $this->assertSame('PlayerMoved', $playerMoved->name());
        $this->assertSame($gameId->toString(), $playerMoved->aggregateId());
        $this->assertSame(Clock::instance()->now(), $playerMoved->occurredOn());
        $this->assertSame($payload, $playerMoved->payload());

        Clock::instance()->resume();
    }
}
