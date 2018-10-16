<?php
declare(strict_types=1);

namespace Gaming\Memory\Domain\Model\Game\Event;

use Gaming\Common\Clock\Clock;
use Gaming\Memory\Domain\Model\Game\GameId;
use PHPUnit\Framework\TestCase;

final class GameClosedTest extends TestCase
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

        $gameClosed = new GameClosed(
            $gameId
        );

        $this->assertSame('GameClosed', $gameClosed->name());
        $this->assertSame($gameId->toString(), $gameClosed->aggregateId());
        $this->assertSame(Clock::instance()->now(), $gameClosed->occurredOn());
        $this->assertSame($payload, $gameClosed->payload());

        Clock::instance()->resume();
    }
}
