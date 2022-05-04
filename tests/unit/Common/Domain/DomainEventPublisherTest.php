<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Common\Domain;

use ArrayIterator;
use Gaming\Common\Domain\DomainEvent;
use Gaming\Common\Domain\DomainEventPublisher;
use Gaming\Common\Domain\DomainEventSubscriber;
use PHPUnit\Framework\TestCase;

final class DomainEventPublisherTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldPublishToSubscribers(): void
    {
        $domainEventToPublish = $this->createMock(DomainEvent::class);

        $firstDomainEventSubscriber = $this->createMock(DomainEventSubscriber::class);
        $firstDomainEventSubscriber
            ->expects($this->once())
            ->method('handle')
            ->with($domainEventToPublish);

        $secondDomainEventSubscriber = $this->createMock(DomainEventSubscriber::class);
        $secondDomainEventSubscriber
            ->expects($this->once())
            ->method('handle')
            ->with($domainEventToPublish);

        $domainEventPublisher = new DomainEventPublisher(
            new ArrayIterator(
                [
                    $firstDomainEventSubscriber,
                    $secondDomainEventSubscriber
                ]
            )
        );

        $domainEventPublisher->publish([$domainEventToPublish]);
    }
}
