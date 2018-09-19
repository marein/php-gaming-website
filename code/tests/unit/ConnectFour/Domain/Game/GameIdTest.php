<?php
declare(strict_types=1);

namespace Gambling\ConnectFour\Domain\Game;

use Gambling\ConnectFour\Domain\Game\Exception\GameNotFoundException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class GameIdTest extends TestCase
{
    /**
     * @test
     */
    public function itCanBeGenerated(): void
    {
        $gameId = GameId::generate();

        $this->assertTrue(Uuid::isValid($gameId->toString()));
    }

    /**
     * @test
     */
    public function itCanBeCreatedFromString(): void
    {
        $expected = Uuid::uuid1()->toString();

        $gameId = GameId::fromString($expected);

        $this->assertSame($expected, $gameId->toString());
    }

    /**
     * @test
     */
    public function itCanBeTypeCastedToString(): void
    {
        $gameId = GameId::generate();

        $this->assertTrue(Uuid::isValid($gameId->toString()));
        $this->assertTrue(Uuid::isValid((string)$gameId));
    }

    /**
     * @test
     * @dataProvider invalidStringProvider
     */
    public function itShouldThrowGameNotFoundExceptionOnInvalidString(string $invalidString): void
    {
        $this->expectException(GameNotFoundException::class);

        GameId::fromString($invalidString);
    }

    /**
     * Returns data for itShouldThrowGameNotFoundExceptionOnInvalidString
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
