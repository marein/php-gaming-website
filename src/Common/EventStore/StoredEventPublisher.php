<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

final class StoredEventPublisher
{
    /**
     * @var StoredEventSubscriber[]
     */
    private array $subscribers;

    public function __construct()
    {
        $this->subscribers = [];
    }

    public function subscribe(StoredEventSubscriber $subscriber): void
    {
        $this->subscribers[] = $subscriber;
    }

    /**
     * @param StoredEvent[] $storedEvents
     */
    public function publish(array $storedEvents): void
    {
        foreach ($storedEvents as $storedEvent) {
            $this->publishSingle($storedEvent);
        }
    }

    private function publishSingle(StoredEvent $storedEvent): void
    {
        foreach ($this->subscribers as $subscriber) {
            if ($subscriber->isSubscribedTo($storedEvent)) {
                $subscriber->handle($storedEvent);
            }
        }
    }
}
