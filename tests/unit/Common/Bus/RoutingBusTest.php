<?php
declare(strict_types=1);

namespace Gaming\Tests\Unit\Common\Bus;

use Gaming\Common\Bus\Exception\MissingHandlerException;
use Gaming\Common\Bus\RoutingBus;
use PHPUnit\Framework\TestCase;

final class RoutingBusTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldThrowMissingHandlerException(): void
    {
        $this->expectException(MissingHandlerException::class);

        (new RoutingBus([]))->handle(
            $this->createMessage('value')
        );
    }

    /**
     * @test
     */
    public function itShouldRouteMessageToHandlerAndReturnItsValue(): void
    {
        $requestMessage = $this->createMessage('Hello');

        $bus = new RoutingBus(
            [
                get_class($requestMessage) => function (object $message) {
                    return $this->createMessage(
                        $message->value . ' World!'
                    );
                }
            ]
        );
        $response = $bus->handle($requestMessage);

        $this->assertSame('Hello World!', $response->value);
    }

    /**
     * Create a test double.
     *
     * @param string $value
     *
     * @return object
     */
    private function createMessage(string $value): object
    {
        $message = new class()
        {
            public $value;
        };

        $message->value = $value;

        return $message;
    }
}
