<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Common\Domain;

use Gaming\Common\Domain\DomainEvent;
use Gaming\Common\Domain\ExtractPayloadForEventSubscriber;
use PHPUnit\Framework\TestCase;

final class ExtractPayloadForEventSubscriberTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldReturnNullIfNoEventIsHandled(): void
    {
        $subscriber = new ExtractPayloadForEventSubscriber(
            'event-name'
        );

        $this->assertNull($subscriber->payload());
    }

    /**
     * @test
     */
    public function itShouldExtractThePayload(): void
    {
        $expected = ['a', 'a', 'a'];
        $domainEvent = $this->createMock(DomainEvent::class);
        $domainEvent
            ->expects($this->once())
            ->method('payload')
            ->willReturn(['a', 'a', 'a']);

        $subscriber = new ExtractPayloadForEventSubscriber(
            'event-name'
        );
        /** @var DomainEvent $domainEvent */
        $subscriber->handle($domainEvent);

        $this->assertSame($expected, $subscriber->payload());
    }

    /**
     * @test
     * @dataProvider isSubscribedToProvider
     *
     * @param string $domainEventName
     * @param string $subscribedEventName
     * @param bool $shouldBeSubscribedTo
     */
    public function itShouldOnlyBeSubscribedToItsEvent(
        string $domainEventName,
        string $subscribedEventName,
        bool $shouldBeSubscribedTo
    ): void {
        $domainEvent = $this->createMock(DomainEvent::class);
        $domainEvent
            ->expects($this->once())
            ->method('name')
            ->willReturn($domainEventName);

        $subscriber = new ExtractPayloadForEventSubscriber(
            $subscribedEventName
        );

        /** @var DomainEvent $domainEvent */
        $this->assertSame($shouldBeSubscribedTo, $subscriber->isSubscribedTo($domainEvent));
    }

    /**
     * Returns data for itShouldOnlyBeSubscribedToItsEvent
     *
     * @return array
     */
    public function isSubscribedToProvider(): array
    {
        return [
            ['event-name', 'another-event-name', false],
            ['event-name', 'event-name', true]
        ];
    }
}
