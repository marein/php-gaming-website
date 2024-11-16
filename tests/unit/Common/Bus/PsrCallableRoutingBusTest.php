<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Common\Bus;

use Gaming\Common\Bus\Exception\BusException;
use Gaming\Common\Bus\PsrCallableRoutingBus;
use Gaming\Common\Bus\TestContainer;
use Gaming\Tests\Unit\Common\Bus\Fixture\EmptyHandler;
use Gaming\Tests\Unit\Common\Bus\Fixture\FirstRequest;
use Gaming\Tests\Unit\Common\Bus\Fixture\SecondRequest;
use Gaming\Tests\Unit\Common\Bus\Fixture\UniversalHandler;
use PHPUnit\Framework\TestCase;

final class PsrCallableRoutingBusTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldThrowMissingHandlerException(): void
    {
        $this->expectException(BusException::class);

        (new PsrCallableRoutingBus(new TestContainer()))->handle(new FirstRequest());
    }

    /**
     * @test
     */
    public function itShouldThrowMissingHandlerExceptionIfHandlerIsNotCallable(): void
    {
        $this->expectException(BusException::class);

        $bus = new PsrCallableRoutingBus(
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
        $bus = new PsrCallableRoutingBus(
            (new TestContainer())
                ->set(FirstRequest::class, new UniversalHandler())
                ->set(SecondRequest::class, static fn(SecondRequest $request): string => 'closure')
        );

        $this->assertSame('__invoke', $bus->handle(new FirstRequest()));
        $this->assertSame('closure', $bus->handle(new SecondRequest()));
    }
}
