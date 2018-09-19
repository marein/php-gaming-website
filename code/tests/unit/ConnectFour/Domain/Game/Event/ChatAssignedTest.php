<?php
declare(strict_types=1);

namespace Gambling\ConnectFour\Domain\Game\Event;

use Gambling\Common\Clock\Clock;
use Gambling\ConnectFour\Domain\Game\GameId;
use PHPUnit\Framework\TestCase;

final class ChatAssignedTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldBeCreatedWithItsValues(): void
    {
        Clock::instance()->freeze();

        $gameId = GameId::generate();
        $chatId = 'chatId';
        $payload = [
            'gameId' => $gameId->toString(),
            'chatId' => $chatId
        ];

        $chatAssigned = new ChatAssigned($gameId, $chatId);

        $this->assertSame('ChatAssigned', $chatAssigned->name());
        $this->assertSame($gameId->toString(), $chatAssigned->aggregateId());
        $this->assertSame(Clock::instance()->now(), $chatAssigned->occurredOn());
        $this->assertSame($payload, $chatAssigned->payload());

        Clock::instance()->resume();
    }
}
