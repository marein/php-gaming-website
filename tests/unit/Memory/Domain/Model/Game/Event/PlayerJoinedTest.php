<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Memory\Domain\Model\Game\Event;

use Gaming\Common\Clock\Clock;
use Gaming\Memory\Domain\Model\Game\Event\PlayerJoined;
use Gaming\Memory\Domain\Model\Game\GameId;
use Gaming\Memory\Domain\Model\Game\Player;
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
        $playerId = 'playerId';
        $payload = [
            'gameId' => $gameId->toString(),
            'playerId' => $playerId
        ];

        $playerJoined = new PlayerJoined(
            $gameId,
            new Player($playerId)
        );

        $this->assertSame('PlayerJoined', $playerJoined->name());
        $this->assertSame($gameId->toString(), $playerJoined->aggregateId());
        $this->assertSame(Clock::instance()->now(), $playerJoined->occurredOn());
        $this->assertSame($payload, $playerJoined->payload());

        Clock::instance()->resume();
    }
}
