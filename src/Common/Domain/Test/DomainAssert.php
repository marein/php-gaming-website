<?php

declare(strict_types=1);

namespace Gaming\Common\Domain\Test;

use Closure;
use Gaming\Common\Domain\Exception\DomainException;
use PHPUnit\Framework\Assert;

final class DomainAssert
{
    /**
     * @param array<string, bool|int|float|string> $expectedParameters
     * @param class-string<DomainException> $expectedException
     */
    public static function expectViolation(
        Closure $action,
        string $expectedIdentifier,
        array $expectedParameters = [],
        string $expectedException = DomainException::class
    ): void {
        try {
            $action();
            Assert::fail('Expected a domain exception.');
        } catch (DomainException $exception) {
            Assert::assertInstanceOf($expectedException, $exception);

            $violation = $exception->violations->first();
            Assert::assertNotNull($violation);
            Assert::assertSame($expectedIdentifier, $violation->identifier);
            Assert::assertSame(
                $expectedParameters,
                array_combine(
                    array_column($violation->parameters, 'name'),
                    array_column($violation->parameters, 'value')
                )
            );
        }
    }
}
