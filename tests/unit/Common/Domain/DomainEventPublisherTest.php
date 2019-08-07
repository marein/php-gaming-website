<?php
declare(strict_types=1);

namespace Gaming\Tests\Unit\Common\Domain;

use Gaming\Common\Domain\DomainEvent;
use Gaming\Common\Domain\DomainEventPublisher;
use Gaming\Common\Domain\DomainEventSubscriber;
use PHPUnit\Framework\TestCase;

final class DomainEventPublisherTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldPublishToSubscriberIfSubscriberIsSubscribedToEvent(): void
    {
        $domainEventToPublish = $this->createMock(DomainEvent::class);

        $domainEventSubscriber = $this->createMock(DomainEventSubscriber::class);
        $domainEventSubscriber
            ->expects($this->once())
            ->method('isSubscribedTo')
            ->willReturn(true);
        $domainEventSubscriber
            ->expects($this->once())
            ->method('handle')
            ->with($domainEventToPublish);

        $domainEventPublisher = new DomainEventPublisher();

        /** @var DomainEventSubscriber $domainEventSubscriber */
        $domainEventPublisher->subscribe(
            $domainEventSubscriber
        );

        $domainEventPublisher->publish([$domainEventToPublish]);
    }

    /**
     * @test
     */
    public function itShouldNotPublishToSubscriberIfSubscriberIsNotSubscribedToEvent(): void
    {
        $domainEventToPublish = $this->createMock(DomainEvent::class);

        $domainEventSubscriber = $this->createMock(DomainEventSubscriber::class);
        $domainEventSubscriber
            ->expects($this->once())
            ->method('isSubscribedTo')
            ->willReturn(false);
        $domainEventSubscriber
            ->expects($this->never())
            ->method('handle');

        $domainEventPublisher = new DomainEventPublisher();

        /** @var DomainEventSubscriber $domainEventSubscriber */
        $domainEventPublisher->subscribe(
            $domainEventSubscriber
        );

        $domainEventPublisher->publish([$domainEventToPublish]);
    }

    /**
     * @test
     */
    public function itShouldPublishToMultipleSubscribers(): void
    {
        $domainEventToPublish = $this->createMock(DomainEvent::class);

        $firstDomainEventSubscriber = $this->createMock(DomainEventSubscriber::class);
        $firstDomainEventSubscriber
            ->expects($this->once())
            ->method('isSubscribedTo')
            ->willReturn(true);
        $firstDomainEventSubscriber
            ->expects($this->once())
            ->method('handle')
            ->with($domainEventToPublish);

        $secondDomainEventSubscriber = $this->createMock(DomainEventSubscriber::class);
        $secondDomainEventSubscriber
            ->expects($this->once())
            ->method('isSubscribedTo')
            ->willReturn(true);
        $secondDomainEventSubscriber
            ->expects($this->once())
            ->method('handle')
            ->with($domainEventToPublish);

        $domainEventPublisher = new DomainEventPublisher();

        /** @var DomainEventSubscriber $firstDomainEventSubscriber */
        $domainEventPublisher->subscribe(
            $firstDomainEventSubscriber
        );
        /** @var DomainEventSubscriber $secondDomainEventSubscriber */
        $domainEventPublisher->subscribe(
            $secondDomainEventSubscriber
        );

        $domainEventPublisher->publish([$domainEventToPublish]);
    }
}
