<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Chat\Application\Event;

use Gaming\Chat\Application\ChatId;
use Gaming\Chat\Application\Event\ChatInitiated;
use Gaming\Common\Clock\Clock;
use PHPUnit\Framework\TestCase;

final class ChatInitiatedTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldBeCreatedWithItsValues(): void
    {
        Clock::instance()->freeze();

        $chatId = ChatId::generate();
        $ownerId = 'ownerId';
        $payload = [
            'chatId' => $chatId->toString(),
            'ownerId' => $ownerId
        ];

        $chatInitiated = new ChatInitiated($chatId, $ownerId);

        $this->assertSame('ChatInitiated', $chatInitiated->name());
        $this->assertSame($chatId->toString(), $chatInitiated->aggregateId());
        $this->assertSame(Clock::instance()->now(), $chatInitiated->occurredOn());
        $this->assertSame($payload, $chatInitiated->payload());

        Clock::instance()->resume();
    }
}
