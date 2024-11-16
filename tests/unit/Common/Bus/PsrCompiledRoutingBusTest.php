<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Common\Bus;

use Gaming\Common\Bus\Exception\BusException;
use Gaming\Common\Bus\PsrCompiledRoutingBus;
use Gaming\Common\Bus\TestContainer;
use Gaming\Tests\Unit\Common\Bus\Fixture\FirstRequest;
use Gaming\Tests\Unit\Common\Bus\Fixture\SecondRequest;
use Gaming\Tests\Unit\Common\Bus\Fixture\ThirdRequest;
use Gaming\Tests\Unit\Common\Bus\Fixture\UniversalHandler;
use PHPUnit\Framework\TestCase;

final class PsrCompiledRoutingBusTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldThrowMissingHandlerException(): void
    {
        $this->expectException(BusException::class);

        (new PsrCompiledRoutingBus(new TestContainer(), []))->handle(new FirstRequest());
    }

    /**
     * @test
     */
    public function itShouldRouteRequestToHandlerAndReturnItsValue(): void
    {
        $handler = new UniversalHandler();
        $bus = new PsrCompiledRoutingBus(
            (new TestContainer())
                ->set('handler', $handler),
            [
                FirstRequest::class => ['handlerId' => 'handler', 'method' => '__invoke'],
                SecondRequest::class => ['handlerId' => 'handler', 'method' => 'secondAndThird'],
                ThirdRequest::class => ['handlerId' => 'handler', 'method' => 'secondAndThird']
            ]
        );

        $this->assertSame('__invoke', $bus->handle(new FirstRequest()));
        $this->assertSame('secondAndThird', $bus->handle(new SecondRequest()));
        $this->assertSame('secondAndThird', $bus->handle(new ThirdRequest()));
    }
}
