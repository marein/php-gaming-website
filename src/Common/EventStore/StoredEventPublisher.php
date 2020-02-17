<?php
declare(strict_types=1);

namespace Gaming\Common\EventStore;

final class StoredEventPublisher
{
    /**
     * @var StoredEventSubscriber[]
     */
    private array $subscribers;

    /**
     * StoredEventPublisher constructor.
     */
    public function __construct()
    {
        $this->subscribers = [];
    }

    /**
     * Add subscriber.
     *
     * @param StoredEventSubscriber $subscriber
     */
    public function subscribe(StoredEventSubscriber $subscriber): void
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
