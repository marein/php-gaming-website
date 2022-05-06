<?php

declare(strict_types=1);

namespace Gaming\Common\Domain;

use Traversable;

final class DomainEventPublisher
{
    /**
     * @param Traversable<DomainEventSubscriber> $subscribers
     */
    public function __construct(
        private readonly Traversable $subscribers
    ) {
    }

    /**
     * @param DomainEvent[] $domainEvents
     */
    public function publish(array $domainEvents): void
    {
        foreach ($domainEvents as $domainEvent) {
            $this->publishSingle($domainEvent);
        }
    }

    private function publishSingle(DomainEvent $domainEvent): void
    {
        foreach ($this->subscribers as $subscriber) {
            $subscriber->handle($domainEvent);
        }
    }
}
