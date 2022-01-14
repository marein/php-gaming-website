<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Chat\Application\Event;

use DateTime;
use DateTimeImmutable;
use Gaming\Chat\Application\ChatId;
use Gaming\Chat\Application\Event\MessageWritten;
use Gaming\Common\Clock\Clock;
use PHPUnit\Framework\TestCase;

final class MessageWrittenTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldBeCreatedWithItsValues(): void
    {
        Clock::instance()->freeze();

        $chatId = ChatId::generate();
        $messageId = 7;
        $ownerId = 'ownerId';
        $authorId = 'authorId';
        $message = 'message';
        $writtenAt = new DateTimeImmutable();
        $payload = [
            'chatId' => $chatId->toString(),
            'messageId' => $messageId,
            'ownerId' => $ownerId,
            'authorId' => $authorId,
            'message' => $message,
            'writtenAt' => $writtenAt->format(DateTime::ATOM)
        ];

        $messageWritten = new MessageWritten(
            $chatId,
            $messageId,
            $ownerId,
            $authorId,
            $message,
            $writtenAt
        );

        $this->assertSame('MessageWritten', $messageWritten->name());
        $this->assertSame($chatId->toString(), $messageWritten->aggregateId());
        $this->assertSame(Clock::instance()->now(), $messageWritten->occurredOn());
        $this->assertSame($payload, $messageWritten->payload());

        Clock::instance()->resume();
    }
}
