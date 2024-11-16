<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Common\Bus;

use Gaming\Common\Bus\Exception\BusException;
use Gaming\Common\Bus\PsrRuntimeRoutingBus;
use Gaming\Common\Bus\TestContainer;
use Gaming\Tests\Unit\Common\Bus\Fixture\EmptyHandler;
use Gaming\Tests\Unit\Common\Bus\Fixture\FirstRequest;
use Gaming\Tests\Unit\Common\Bus\Fixture\SecondRequest;
use Gaming\Tests\Unit\Common\Bus\Fixture\ThirdRequest;
use Gaming\Tests\Unit\Common\Bus\Fixture\UniversalHandler;
use PHPUnit\Framework\TestCase;

final class PsrRuntimeRoutingBusTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldThrowMissingHandlerException(): void
    {
        $this->expectException(BusException::class);

        (new PsrRuntimeRoutingBus(new TestContainer()))->handle(new FirstRequest());
    }

    /**
     * @test
     */
    public function itShouldThrowMissingHandlerExceptionIfNoMethodExists(): void
    {
        $this->expectException(BusException::class);

        $bus = new PsrRuntimeRoutingBus(
            (new TestContainer())
                ->set(FirstRequest::class, new EmptyHandler())
        );

        $bus->handle(new FirstRequest());
    }

    /**
     * @test
     */
    public function itShouldRouteRequestToHandlerAndReturnItsValue(): void
    {
        $handler = new UniversalHandler();
        $bus = new PsrRuntimeRoutingBus(
            (new TestContainer())
                ->set(FirstRequest::class, $handler)
                ->set(SecondRequest::class, $handler)
                ->set(ThirdRequest::class, $handler)
        );

        $this->assertSame('__invoke', $bus->handle(new FirstRequest()));
        $this->assertSame('secondAndThird', $bus->handle(new SecondRequest()));
        $this->assertSame('secondAndThird', $bus->handle(new ThirdRequest()));
    }
}
