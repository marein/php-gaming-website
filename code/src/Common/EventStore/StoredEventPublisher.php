<?php

namespace Gambling\Common\EventStore;

final class StoredEventPublisher
{
    /**
     * @var StoredEventSubscriber[]
     */
    private $subscribers;

    /**
     * Add subscriber.
     *
     * @param StoredEventSubscriber $subscriber
     */
    public function subscribe(StoredEventSubscriber $subscriber)
    {
        $this->subscribers[] = $subscriber;
    }

    /**
     * Publish the given stored events.
     *
     * @param StoredEvent[] $storedEvents
     */
    public function publish(array $storedEvents): void
    {
        foreach ($storedEvents as $storedEvent) {
            $this->publishSingle($storedEvent);
        }
    }

    /**
     * Publish the given stored event.
     *
     * @param StoredEvent $storedEvent
     */
    private function publishSingle(StoredEvent $storedEvent): void
    {
        foreach ($this->subscribers as $subscriber) {
            if ($subscriber->isSubscribedTo($storedEvent)) {
                $subscriber->handle($storedEvent);
            }
        }
    }
}
