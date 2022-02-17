<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

final class StoredEventPublisher
{
    /**
     * @param StoredEventSubscriber[] $subscribers
     */
    public function __construct(
        private readonly array $subscribers
    ) {
    }

    public function publish(StoredEvent $storedEvent): void
    {
        foreach ($this->subscribers as $subscriber) {
            $subscriber->handle($storedEvent);
        }
    }
}
