<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

use Gaming\Common\Domain\DomainEvent;

final class CompositeStoredEventSubscriber implements StoredEventSubscriber
{
    /**
     * @param StoredEventSubscriber[] $subscribers
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

    public function commit(): void
    {
        foreach ($this->subscribers as $subscriber) {
            $subscriber->commit();
        }
    }
}
