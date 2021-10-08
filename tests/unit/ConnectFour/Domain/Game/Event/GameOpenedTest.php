<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game\Event;

use Gaming\Common\Clock\Clock;
use Gaming\ConnectFour\Domain\Game\Board\Size;
use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\Event\GameOpened;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\Player;
use PHPUnit\Framework\TestCase;

final class GameOpenedTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldBeCreatedWithItsValues(): void
    {
        Clock::instance()->freeze();

        $gameId = GameId::generate();
        $width = 7;
        $height = 6;
        $playerId = 'playerId';
        $payload = [
            'gameId' => $gameId->toString(),
            'width' => $width,
            'height' => $height,
            'playerId' => $playerId
        ];

        $gameOpened = new GameOpened(
            $gameId,
            new Size($width, $height),
            new Player($playerId, Stone::red())
        );

        $this->assertSame('GameOpened', $gameOpened->name());
        $this->assertSame($gameId->toString(), $gameOpened->aggregateId());
        $this->assertSame(Clock::instance()->now(), $gameOpened->occurredOn());
        $this->assertSame($payload, $gameOpened->payload());

        Clock::instance()->resume();
    }
}
