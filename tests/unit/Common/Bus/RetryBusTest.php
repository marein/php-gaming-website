<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Common\Bus;

use Gaming\Common\Bus\Bus;
use Gaming\Common\Bus\RetryBus;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class RetryBusTest extends TestCase
{
    /**
     * @test
     */
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

    /**
     * @test
     */
    public function itShouldRetry(): void
    {
        $actionToCall = static function (): void {
            // No op
        };

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

        $retryBus->handle($actionToCall);
    }

    /**
     * @test
     */
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

        $retryBus->handle(
            static function (): void {
                // No op
            }
        );
    }

    /**
     * @test
     */
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

        $retryBus->handle(
            static function (): void {
                // No op
            }
        );
    }
}
