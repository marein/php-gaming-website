<?php

namespace Gambling\Common\Bus;

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
            0
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
                new \Exception()
            );
        $bus
            ->expects($this->at(1))
            ->method('handle')
            ->willThrowException(
                new \Exception()
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
            3
        );

        $retryBus->handle($actionToCall);
    }

    /**
     * @test
     */
    public function itShouldThrowTheApplicationExceptionWhenNumberOfRetriesAreReached(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Custom exception');

        $bus = $this->createMock(Bus::class);

        // "handle" always throws an exception.
        $bus
            ->expects($this->any())
            ->method('handle')
            ->willThrowException(
                new \Exception('Custom exception')
            );

        // Expect that "handle" is called three times.
        $bus
            ->expects($this->exactly(3))
            ->method('handle');

        /** @var Bus $bus */
        $retryBus = new RetryBus(
            $bus,
            3
        );

        $retryBus->handle(function () {
            // No op
        });
    }
}
