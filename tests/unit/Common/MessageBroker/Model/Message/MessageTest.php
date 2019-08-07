<?php
declare(strict_types=1);

namespace Gaming\Tests\Unit\Common\MessageBroker\Model\Message;

use Gaming\Common\MessageBroker\Model\Message\Message;
use Gaming\Common\MessageBroker\Model\Message\Name;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldBeCreatedWithItsValues(): void
    {
        $expectedName = Name::fromString('MyDomain.MyMessage');
        $expectedBody = 'payload';

        $message = new Message(
            $expectedName,
            $expectedBody
        );

        $this->assertSame($expectedName, $message->name());
        $this->assertSame($expectedBody, $message->body());
    }
}
