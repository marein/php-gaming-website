<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Common\Bus;

use Gaming\Common\Bus\Exception\BusException;
use Gaming\Common\Bus\Request;
use Gaming\Common\Bus\RoutingBus;
use PHPUnit\Framework\TestCase;

final class RoutingBusTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldThrowMissingHandlerException(): void
    {
        $this->expectException(BusException::class);

        (new RoutingBus([]))->handle(
            $this->createRequest('value')
        );
    }

    /**
     * @test
     */
    public function itShouldRouteRequestToHandlerAndReturnItsValue(): void
    {
        $request = $this->createRequest('Hello');

        $bus = new RoutingBus(
            [
                $request::class => fn(Request $request): object => $this->createRequest(
                    $request->value . ' World!'
                )
            ]
        );
        $response = $bus->handle($request);

        $this->assertSame('Hello World!', $response->value);
    }

    private function createRequest(string $value): Request
    {
        return new class ($value) implements Request {
            public function __construct(
                public readonly string $value
            ) {
            }
        };
    }
}
