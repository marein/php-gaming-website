<?php

namespace Gambling\ConnectFour\Domain\Game;

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
}
