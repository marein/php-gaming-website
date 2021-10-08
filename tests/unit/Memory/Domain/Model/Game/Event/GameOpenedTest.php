<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Memory\Domain\Model\Game\Event;

use Gaming\Common\Clock\Clock;
use Gaming\Memory\Domain\Model\Game\Event\GameOpened;
use Gaming\Memory\Domain\Model\Game\GameId;
use Gaming\Memory\Domain\Model\Game\Player;
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
        $numberOfCards = 42;
        $playerId = 'playerId';
        $payload = [
            'gameId' => $gameId->toString(),
            'numberOfCards' => $numberOfCards,
            'playerId' => $playerId
        ];

        $gameOpened = new GameOpened(
            $gameId,
            $numberOfCards,
            new Player($playerId)
        );

        $this->assertSame('GameOpened', $gameOpened->name());
        $this->assertSame($gameId->toString(), $gameOpened->aggregateId());
        $this->assertSame(Clock::instance()->now(), $gameOpened->occurredOn());
        $this->assertSame($payload, $gameOpened->payload());

        Clock::instance()->resume();
    }
}
