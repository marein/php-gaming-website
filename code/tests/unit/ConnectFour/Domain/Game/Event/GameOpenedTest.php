<?php
declare(strict_types=1);

namespace Gambling\ConnectFour\Domain\Game\Event;

use Gambling\Common\Clock\Clock;
use Gambling\ConnectFour\Domain\Game\Board\Size;
use Gambling\ConnectFour\Domain\Game\Board\Stone;
use Gambling\ConnectFour\Domain\Game\GameId;
use Gambling\ConnectFour\Domain\Game\Player;
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
            'gameId'   => $gameId->toString(),
            'width'    => $width,
            'height'   => $height,
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
