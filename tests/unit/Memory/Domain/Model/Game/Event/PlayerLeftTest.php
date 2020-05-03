<?php
declare(strict_types=1);

namespace Gaming\Tests\Unit\Memory\Domain\Model\Game\Event;

use Gaming\Common\Clock\Clock;
use Gaming\Memory\Domain\Model\Game\Event\PlayerLeft;
use Gaming\Memory\Domain\Model\Game\GameId;
use Gaming\Memory\Domain\Model\Game\Player;
use PHPUnit\Framework\TestCase;

final class PlayerLeftTest extends TestCase
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

        $playerLeft = new PlayerLeft(
            $gameId,
            new Player($playerId)
        );

        $this->assertSame('PlayerLeft', $playerLeft->name());
        $this->assertSame($gameId->toString(), $playerLeft->aggregateId());
        $this->assertSame(Clock::instance()->now(), $playerLeft->occurredOn());
        $this->assertSame($payload, $playerLeft->payload());

        Clock::instance()->resume();
    }
}
