<?php
declare(strict_types=1);

namespace Gaming\Memory\Domain\Model\Game\Event;

use Gaming\Common\Clock\Clock;
use Gaming\Memory\Domain\Model\Game\GameId;
use PHPUnit\Framework\TestCase;

final class GameStartedTest extends TestCase
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

        $gameStarted = new GameStarted(
            $gameId
        );

        $this->assertSame('GameStarted', $gameStarted->name());
        $this->assertSame($gameId->toString(), $gameStarted->aggregateId());
        $this->assertSame(Clock::instance()->now(), $gameStarted->occurredOn());
        $this->assertSame($payload, $gameStarted->payload());

        Clock::instance()->resume();
    }
}
