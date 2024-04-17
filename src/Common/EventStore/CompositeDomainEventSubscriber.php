<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

final class CompositeDomainEventSubscriber implements DomainEventSubscriber
{
    /**
     * @param DomainEventSubscriber[] $subscribers
     */
    public function __construct(
        private readonly array $subscribers
    ) {
    }

    public function handle(DomainEvent $domainEvent): void
    {
        foreach ($this->subscribers as $subscriber) {
            $subscriber->handle($domainEvent);
        }
    }
}
