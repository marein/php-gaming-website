<?php
declare(strict_types=1);

namespace Gaming\Tests\Unit\Common\MessageBroker\Model\Message;

use Gaming\Common\MessageBroker\Exception\InvalidDomainException;
use Gaming\Common\MessageBroker\Exception\InvalidFormatException;
use Gaming\Common\MessageBroker\Exception\InvalidNameException;
use Gaming\Common\MessageBroker\Model\Message\Name;
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
        $this->assertSame(self::VALID_DOMAIN . '.' . self::VALID_NAME, (string)$name);
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

    /**
     * @test
     */
    public function itShouldBeCreatedFromString(): void
    {
        $name = Name::fromString(self::VALID_DOMAIN . '.' . self::VALID_NAME);

        $this->assertSame(self::VALID_DOMAIN, $name->domain());
        $this->assertSame(self::VALID_NAME, $name->name());
        $this->assertSame(self::VALID_DOMAIN . '.' . self::VALID_NAME, (string)$name);
    }

    /**
     * @test
     */
    public function itShouldThrowInvalidFormatOnInvalidFormat(): void
    {
        $this->expectException(InvalidFormatException::class);

        Name::fromString('MyDomainMyName');
    }

    /**
     * @test
     * @dataProvider invalidDomainsProvider
     */
    public function itShouldThrowInvalidDomainOnInvalidDomainsFromString(string $domain): void
    {
        $this->expectException(InvalidDomainException::class);

        Name::fromString($domain . '.' . self::VALID_NAME);
    }

    /**
     * @test
     * @dataProvider invalidNamesProvider
     */
    public function itShouldThrowInvalidNameOnInvalidNamesFromString(string $name): void
    {
        $this->expectException(InvalidNameException::class);

        Name::fromString(self::VALID_DOMAIN . '.' . $name);
    }

    /**
     * Returns invalid domains.
     *
     * @return array
     */
    public function invalidDomainsProvider(): array
    {
        return [
            ['invalid-domain'],
            ['invalid_domain'],
            ['invalidDomain'],
            ['Invalid1234Domain'],
        ];
    }

    /**
     * Returns invalid names.
     *
     * @return array
     */
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
