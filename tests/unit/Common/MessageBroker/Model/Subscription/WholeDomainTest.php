<?php
declare(strict_types=1);

namespace Gaming\Tests\Unit\Common\MessageBroker\Model\Subscription;

use Gaming\Common\MessageBroker\Exception\InvalidDomainException;
use Gaming\Common\MessageBroker\Model\Subscription\WholeDomain;
use PHPUnit\Framework\TestCase;

class WholeDomainTest extends TestCase
{
    private const VALID_DOMAIN = 'MyDomain';

    /**
     * @test
     */
    public function itShouldBeCreatedWithItsValues(): void
    {
        $name = new WholeDomain(self::VALID_DOMAIN);

        $this->assertSame(self::VALID_DOMAIN, $name->domain());
    }

    /**
     * @test
     * @dataProvider invalidDomainsProvider
     */
    public function itShouldThrowInvalidDomainOnInvalidDomains(string $domain): void
    {
        $this->expectException(InvalidDomainException::class);

        new WholeDomain($domain);
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
}
