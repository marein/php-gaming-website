<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Chat\Application;

use Codeception\Attribute\DataProvider;
use Gaming\Chat\Application\ChatId;
use Gaming\Chat\Application\Exception\ChatNotFoundException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class ChatIdTest extends TestCase
{
    #[Test]
    public function itCanBeGenerated(): void
    {
        $chatId = ChatId::generate();

        $this->assertTrue(Uuid::isValid($chatId->toString()));
    }

    #[Test]
    public function itCanBeCreatedFromString(): void
    {
        $expected = Uuid::v6()->toRfc4122();

        $chatId = ChatId::fromString($expected);

        $this->assertSame($expected, $chatId->toString());
    }

    #[Test]
    public function itCanBeTypeCastedToString(): void
    {
        $chatId = ChatId::generate();

        $this->assertTrue(Uuid::isValid($chatId->toString()));
        $this->assertTrue(Uuid::isValid((string)$chatId));
    }

    #[Test]
    #[DataProvider('invalidStringProvider')]
    public function itShouldThrowChatNotFoundExceptionOnInvalidString(string $invalidString): void
    {
        $this->expectException(ChatNotFoundException::class);

        ChatId::fromString($invalidString);
    }

    /**
     * Returns data for itShouldThrowChatNotFoundExceptionOnInvalidString
     */
    public function invalidStringProvider(): array
    {
        return [
            ['invalid id'],
            ['another-invalid-id'],
            [uniqid()]
        ];
    }
}
