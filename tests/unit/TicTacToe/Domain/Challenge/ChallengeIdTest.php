<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\TicTacToe\Domain\Challenge;

use Codeception\Attribute\DataProvider;
use Gaming\Common\Domain\Test\DomainAssert;
use Gaming\TicTacToe\Domain\Challenge\ChallengeId;
use Gaming\TicTacToe\Domain\Challenge\Exception\ChallengeNotFoundException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class ChallengeIdTest extends TestCase
{
    #[Test]
    public function itCanBeGenerated(): void
    {
        $challengeId = ChallengeId::generate();

        $this->assertTrue(Uuid::isValid($challengeId->toString()));
    }

    #[Test]
    public function itCanBeCreatedFromString(): void
    {
        $expected = Uuid::v6()->toRfc4122();

        $challengeId = ChallengeId::fromString($expected);

        $this->assertSame($expected, $challengeId->toString());
    }

    #[Test]
    public function itCanBeTypeCastedToString(): void
    {
        $challengeId = ChallengeId::generate();

        $this->assertTrue(Uuid::isValid($challengeId->toString()));
        $this->assertTrue(Uuid::isValid((string)$challengeId));
    }

    #[Test]
    #[DataProvider('invalidStringProvider')]
    public function itShouldThrowChallengeNotFoundExceptionOnInvalidString(string $invalidString): void
    {
        DomainAssert::expectViolation(
            fn() => ChallengeId::fromString($invalidString),
            ChallengeNotFoundException::class,
            'challenge_not_found'
        );
    }

    public function invalidStringProvider(): array
    {
        return [
            ['invalid id'],
            ['another-invalid-id'],
            [uniqid()]
        ];
    }
}
