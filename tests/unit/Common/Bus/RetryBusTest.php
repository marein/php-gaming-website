<?php
declare(strict_types=1);

namespace Gaming\Common\Bus;

use PHPUnit\Framework\TestCase;

final class RetryBusTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldThrowAnExceptionIfNumberOfRetriesIsLowerThanOne(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        /** @var Bus $bus */
        $bus = $this->createMock(Bus::class);

        new RetryBus(
            $bus,
            0,
            \RuntimeException::class
        );
    }

    /**
     * @test
     */
    public function itShouldRetry(): void
    {
        $actionToCall = function () {
            // No op
        };

        $bus = $this->createMock(Bus::class);

        // The first two times when "handle" is called, an exception is thrown.
        $bus
            ->expects($this->at(0))
            ->method('handle')
            ->willThrowException(
                new \RuntimeException()
            );
        $bus
            ->expects($this->at(1))
            ->method('handle')
            ->willThrowException(
                new \RuntimeException()
            );
        $bus
            ->expects($this->at(2))
            ->method('handle')
            ->with($actionToCall);

        // Expect that "handle" is called three times.
        $bus
            ->expects($this->exactly(3))
            ->method('handle');

        /** @var Bus $bus */
        $retryBus = new RetryBus(
            $bus,
            3,
            \RuntimeException::class
        );

        $retryBus->handle($actionToCall);
    }

    /**
     * @test
     */
    public function itShouldThrowTheApplicationExceptionWhenNumberOfRetriesAreReached(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Custom exception');

        $bus = $this->createMock(Bus::class);

        // "handle" always throws an exception.
        $bus
            ->expects($this->any())
            ->method('handle')
            ->willThrowException(
                new \RuntimeException('Custom exception')
            );

        // Expect that "handle" is called three times.
        $bus
            ->expects($this->exactly(3))
            ->method('handle');

        /** @var Bus $bus */
        $retryBus = new RetryBus(
            $bus,
            3,
            \RuntimeException::class
        );

        $retryBus->handle(function () {
            // No op
        });
    }

    /**
     * @test
     */
    public function itShouldThrowTheApplicationExceptionIfItsNotTheConfiguredException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Custom exception');

        $bus = $this->createMock(Bus::class);

        // "handle" always throws an exception.
        $bus
            ->expects($this->any())
            ->method('handle')
            ->willThrowException(
                new \RuntimeException('Custom exception')
            );

        // Expect that "handle" is called one time.
        $bus
            ->expects($this->exactly(1))
            ->method('handle');

        /** @var Bus $bus */
        $retryBus = new RetryBus(
            $bus,
            3,
            \InvalidArgumentException::class
        );

        $retryBus->handle(function () {
            // No op
        });
    }
}
