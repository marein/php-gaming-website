<?php
declare(strict_types=1);

namespace Gaming\Tests\Unit\Memory\Domain\Model\Game\Event;

use Gaming\Common\Clock\Clock;
use Gaming\Memory\Domain\Model\Game\Event\PlayerLeaved;
use Gaming\Memory\Domain\Model\Game\GameId;
use Gaming\Memory\Domain\Model\Game\Player;
use PHPUnit\Framework\TestCase;

final class PlayerLeavedTest extends TestCase
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
            'gameId'   => $gameId->toString(),
            'playerId' => $playerId
        ];

        $playerLeaved = new PlayerLeaved(
            $gameId,
            new Player($playerId)
        );

        $this->assertSame('PlayerLeaved', $playerLeaved->name());
        $this->assertSame($gameId->toString(), $playerLeaved->aggregateId());
        $this->assertSame(Clock::instance()->now(), $playerLeaved->occurredOn());
        $this->assertSame($payload, $playerLeaved->payload());

        Clock::instance()->resume();
    }
}
