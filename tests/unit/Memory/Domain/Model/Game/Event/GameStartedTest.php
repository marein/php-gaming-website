<?php
declare(strict_types=1);

namespace Gaming\Tests\Unit\Memory\Domain\Model\Game\Event;

use Gaming\Common\Clock\Clock;
use Gaming\Memory\Domain\Model\Game\Event\GameStarted;
use Gaming\Memory\Domain\Model\Game\GameId;
use Gaming\Memory\Domain\Model\Game\Player;
use Gaming\Memory\Domain\Model\Game\PlayerPool;
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
        $playerPool = PlayerPool::beginWith(
            new Player('playerId1')
        );
        $playerPool = $playerPool->join(
            new Player('playerId2')
        );
        $payload = [
            'gameId'    => $gameId->toString(),
            'playerIds' => ['playerId1', 'playerId2']
        ];

        $gameStarted = new GameStarted(
            $gameId,
            $playerPool
        );

        $this->assertSame('GameStarted', $gameStarted->name());
        $this->assertSame($gameId->toString(), $gameStarted->aggregateId());
        $this->assertSame(Clock::instance()->now(), $gameStarted->occurredOn());
        $this->assertSame($payload, $gameStarted->payload());

        Clock::instance()->resume();
    }
}
