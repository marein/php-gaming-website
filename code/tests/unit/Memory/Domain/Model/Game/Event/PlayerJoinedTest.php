<?php
declare(strict_types=1);

namespace Gambling\Memory\Domain\Model\Game\Event;

use Gambling\Common\Clock\Clock;
use Gambling\Memory\Domain\Model\Game\GameId;
use Gambling\Memory\Domain\Model\Game\Player;
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
            'gameId'        => $gameId->toString(),
            'playerId'      => $playerId
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
