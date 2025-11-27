<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Identity\Domain\Model\Account;

use Gaming\Identity\Domain\Model\Account\AccountId;
use Gaming\Identity\Domain\Model\Account\Exception\AccountNotFoundException;
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
     * @dataProvider invalidStringProvider
     */
    public function itShouldThrowUserNotFoundExceptionOnInvalidString(string $invalidString): void
    {
        $this->expectException(AccountNotFoundException::class);

        AccountId::fromString($invalidString);
    }

    /**
     * Returns data for itShouldThrowUserNotFoundExceptionOnInvalidString
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
