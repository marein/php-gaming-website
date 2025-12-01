<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Identity\Domain\Model\Account;

use Gaming\Identity\Domain\Model\Account\AccountId;
use Gaming\Identity\Domain\Model\Account\Exception\AccountNotFoundException;
use Gaming\Identity\Domain\Model\User\Exception\UserNotFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class AccountIdTest extends TestCase
{
    /**
     * @test
     */
    public function itCanBeGenerated(): void
    {
        $accountId = AccountId::generate();

        $this->assertTrue(Uuid::isValid($accountId->toString()));
    }

    /**
     * @test
     */
    public function itCanBeCreatedFromString(): void
    {
        $expected = Uuid::v6()->toRfc4122();

        $accountId = AccountId::fromString($expected);

        $this->assertSame($expected, $accountId->toString());
    }

    /**
     * @test
     */
    public function itCanBeTypeCastedToString(): void
    {
        $accountId = AccountId::generate();

        $this->assertTrue(Uuid::isValid($accountId->toString()));
        $this->assertTrue(Uuid::isValid((string)$accountId));
    }

    /**
     * @test
     * @dataProvider invalidIdProvider
     */
    public function itShouldThrowNotFoundExceptionOnInvalidId(
        string $methodName,
        string $invalidId,
        string $exceptionClass
    ): void {
        $this->expectException($exceptionClass);

        AccountId::$methodName($invalidId);
    }

    /**
     * Returns data for itShouldThrowNotFoundExceptionOnInvalidId
     */
    public function invalidIdProvider(): array
    {
        return [
            ['fromString', 'invalid id', AccountNotFoundException::class],
            ['fromString', 'another-invalid-id', AccountNotFoundException::class],
            ['fromString', uniqid(), AccountNotFoundException::class],
            ['forUserId', 'invalid id', UserNotFoundException::class],
            ['forUserId', 'another-invalid-id', UserNotFoundException::class],
            ['forUserId', uniqid(), UserNotFoundException::class]
        ];
    }
}
