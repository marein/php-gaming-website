<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game;

use Codeception\Attribute\DataProvider;
use Gaming\ConnectFour\Domain\Game\Exception\GameNotFoundException;
use Gaming\ConnectFour\Domain\Game\GameId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class GameIdTest extends TestCase
{
    #[Test]
    public function itCanBeGenerated(): void
    {
        $gameId = GameId::generate();

        $this->assertTrue(Uuid::isValid($gameId->toString()));
    }

    #[Test]
    public function itCanBeCreatedFromString(): void
    {
        $expected = Uuid::v6()->toRfc4122();

        $gameId = GameId::fromString($expected);

        $this->assertSame($expected, $gameId->toString());
    }

    #[Test]
    public function itCanBeTypeCastedToString(): void
    {
        $gameId = GameId::generate();

        $this->assertTrue(Uuid::isValid($gameId->toString()));
        $this->assertTrue(Uuid::isValid((string)$gameId));
    }

    #[Test]
    #[DataProvider('invalidStringProvider')]
    public function itShouldThrowGameNotFoundExceptionOnInvalidString(string $invalidString): void
    {
        $this->expectException(GameNotFoundException::class);

        GameId::fromString($invalidString);
    }

    /**
     * Returns data for itShouldThrowGameNotFoundExceptionOnInvalidString
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
