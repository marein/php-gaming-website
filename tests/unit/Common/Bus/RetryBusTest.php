<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Common\Bus;

use Gaming\Common\Bus\Bus;
use Gaming\Common\Bus\Request;
use Gaming\Common\Bus\RetryBus;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class RetryBusTest extends TestCase
{
    #[Test]
    public function itShouldThrowAnExceptionIfNumberOfRetriesIsLowerThanOne(): void
    {
        $this->expectException(InvalidArgumentException::class);

        /** @var Bus $bus */
        $bus = $this->createMock(Bus::class);

        new RetryBus(
            $bus,
            0,
            RuntimeException::class
        );
    }

    #[Test]
    public function itShouldRetry(): void
    {
        $bus = $this->createMock(Bus::class);

        // The first two times when "handle" is called, an exception is thrown.
        $bus
            ->method('handle')
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new RuntimeException()),
                $this->throwException(new RuntimeException()),
                null
            );

        // Expect that "handle" is called three times.
        $bus
            ->expects($this->exactly(3))
            ->method('handle');

        /** @var Bus $bus */
        $retryBus = new RetryBus(
            $bus,
            3,
            RuntimeException::class
        );

        $retryBus->handle($this->createRequest());
    }

    #[Test]
    public function itShouldThrowTheApplicationExceptionWhenNumberOfRetriesAreReached(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Custom exception');

        $bus = $this->createMock(Bus::class);

        // "handle" always throws an exception.
        $bus
            ->method('handle')
            ->willThrowException(
                new RuntimeException('Custom exception')
            );

        // Expect that "handle" is called three times.
        $bus
            ->expects($this->exactly(3))
            ->method('handle');

        /** @var Bus $bus */
        $retryBus = new RetryBus(
            $bus,
            3,
            RuntimeException::class
        );

        $retryBus->handle($this->createRequest());
    }

    #[Test]
    public function itShouldThrowTheApplicationExceptionIfItsNotTheConfiguredException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Custom exception');

        $bus = $this->createMock(Bus::class);

        // "handle" always throws an exception.
        $bus
            ->method('handle')
            ->willThrowException(
                new RuntimeException('Custom exception')
            );

        // Expect that "handle" is called one time.
        $bus
            ->expects($this->once())
            ->method('handle');

        /** @var Bus $bus */
        $retryBus = new RetryBus(
            $bus,
            3,
            InvalidArgumentException::class
        );

        $retryBus->handle($this->createRequest());
    }

    private function createRequest(): Request
    {
        return new class () implements Request {
        };
    }
}
