<?php

namespace Gambling\Chat\Application;

use Gambling\Chat\Application\Exception\ChatNotFoundException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class ChatIdTest extends TestCase
{
    /**
     * @test
     */
    public function itCanBeGenerated(): void
    {
        $chatId = ChatId::generate();

        $this->assertTrue(Uuid::isValid($chatId->toString()));
    }

    /**
     * @test
     */
    public function itCanBeCreatedFromString(): void
    {
        $expected = Uuid::uuid1()->toString();

        $chatId = ChatId::fromString($expected);

        $this->assertSame($expected, $chatId->toString());
    }

    /**
     * @test
     */
    public function itCanBeTypeCastedToString(): void
    {
        $chatId = ChatId::generate();

        $this->assertTrue(Uuid::isValid($chatId->toString()));
        $this->assertTrue(Uuid::isValid((string)$chatId));
    }

    /**
     * @test
     * @dataProvider invalidStringProvider
     */
    public function itShouldThrowChatNotFoundExceptionOnInvalidString(string $invalidString): void
    {
        $this->expectException(ChatNotFoundException::class);

        ChatId::fromString($invalidString);
    }

    /**
     * Returns data for itShouldThrowChatNotFoundExceptionOnInvalidString
     *
     * @return array
     */
    public function invalidStringProvider(): array
    {
        return [
            ['invalid id'],
            ['another-invalid-id'],
            [uniqid()],
            [Uuid::uuid4()->toString()]
        ];
    }
}
