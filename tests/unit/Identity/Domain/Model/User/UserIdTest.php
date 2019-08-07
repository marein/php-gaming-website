<?php
declare(strict_types=1);

namespace Gaming\Tests\Unit\Identity\Domain\Model\User;

use Gaming\Identity\Domain\Model\User\Exception\UserNotFoundException;
use Gaming\Identity\Domain\Model\User\UserId;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class UserIdTest extends TestCase
{
    /**
     * @test
     */
    public function itCanBeGenerated(): void
    {
        $userId = UserId::generate();

        $this->assertTrue(Uuid::isValid($userId->toString()));
    }

    /**
     * @test
     */
    public function itCanBeCreatedFromString(): void
    {
        $expected = Uuid::uuid1()->toString();

        $userId = UserId::fromString($expected);

        $this->assertSame($expected, $userId->toString());
    }

    /**
     * @test
     */
    public function itCanBeTypeCastedToString(): void
    {
        $userId = UserId::generate();

        $this->assertTrue(Uuid::isValid($userId->toString()));
        $this->assertTrue(Uuid::isValid((string)$userId));
    }

    /**
     * @test
     * @dataProvider invalidStringProvider
     */
    public function itShouldThrowUserNotFoundExceptionOnInvalidString(string $invalidString): void
    {
        $this->expectException(UserNotFoundException::class);

        UserId::fromString($invalidString);
    }

    /**
     * Returns data for itShouldThrowUserNotFoundExceptionOnInvalidString
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
