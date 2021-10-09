<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Common\MessageBroker\Model\Consumer;

use Gaming\Common\MessageBroker\Exception\InvalidDomainException;
use Gaming\Common\MessageBroker\Exception\InvalidNameException;
use Gaming\Common\MessageBroker\Model\Consumer\Name;
use PHPUnit\Framework\TestCase;

class NameTest extends TestCase
{
    private const VALID_DOMAIN = 'MyDomain';
    private const VALID_NAME = 'MyName';

    /**
     * @test
     */
    public function itShouldBeCreatedWithItsValues(): void
    {
        $name = new Name(self::VALID_DOMAIN, self::VALID_NAME);

        $this->assertSame(self::VALID_DOMAIN, $name->domain());
        $this->assertSame(self::VALID_NAME, $name->name());
    }

    /**
     * @test
     * @dataProvider invalidDomainsProvider
     */
    public function itShouldThrowInvalidDomainOnInvalidDomains(string $domain): void
    {
        $this->expectException(InvalidDomainException::class);

        new Name($domain, self::VALID_NAME);
    }

    /**
     * @test
     * @dataProvider invalidNamesProvider
     */
    public function itShouldThrowInvalidNameOnInvalidNames(string $name): void
    {
        $this->expectException(InvalidNameException::class);

        new Name(self::VALID_DOMAIN, $name);
    }

    public function invalidDomainsProvider(): array
    {
        return [
            ['invalid-domain'],
            ['invalid_domain'],
            ['invalidDomain'],
            ['Invalid1234Domain'],
        ];
    }

    public function invalidNamesProvider(): array
    {
        return [
            ['invalid-name'],
            ['invalid_name'],
            ['invalidName'],
            ['Invalid1234Name'],
        ];
    }
}
