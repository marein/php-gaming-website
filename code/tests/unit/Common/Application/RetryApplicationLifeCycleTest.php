<?php

namespace Gambling\Common\Application;

use PHPUnit\Framework\TestCase;

final class RetryApplicationLifeCycleTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldThrowAnExceptionIfNumberOfRetriesIsLowerThanOne(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        /** @var ApplicationLifeCycle $applicationLifeCycle */
        $applicationLifeCycle = $this->createMock(ApplicationLifeCycle::class);

        new RetryApplicationLifeCycle(
            $applicationLifeCycle,
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

        $applicationLifeCycle = $this->createMock(ApplicationLifeCycle::class);

        // The first two times when "run" is called, an exception is thrown.
        $applicationLifeCycle
            ->expects($this->at(0))
            ->method('run')
            ->willThrowException(
                new \Exception()
            );
        $applicationLifeCycle
            ->expects($this->at(1))
            ->method('run')
            ->willThrowException(
                new \Exception()
            );
        $applicationLifeCycle
            ->expects($this->at(2))
            ->method('run')
            ->with($actionToCall);

        // Expect that "run" is called three times.
        $applicationLifeCycle
            ->expects($this->exactly(3))
            ->method('run');

        /** @var ApplicationLifeCycle $applicationLifeCycle */
        $retryApplicationLifeCycle = new RetryApplicationLifeCycle(
            $applicationLifeCycle,
            3
        );

        $retryApplicationLifeCycle->run($actionToCall);
    }

    /**
     * @test
     */
    public function itShouldThrowTheApplicationExceptionWhenNumberOfRetriesAreReached(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Custom exception');

        $applicationLifeCycle = $this->createMock(ApplicationLifeCycle::class);

        // "run" always throws an exception.
        $applicationLifeCycle
            ->expects($this->any())
            ->method('run')
            ->willThrowException(
                new \Exception('Custom exception')
            );

        // Expect that "run" is called three times.
        $applicationLifeCycle
            ->expects($this->exactly(3))
            ->method('run');

        /** @var ApplicationLifeCycle $applicationLifeCycle */
        $retryApplicationLifeCycle = new RetryApplicationLifeCycle(
            $applicationLifeCycle,
            3
        );

        $retryApplicationLifeCycle->run(function () {
            // No op
        });
    }
}
