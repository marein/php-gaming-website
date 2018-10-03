<?php
declare(strict_types=1);

namespace Gaming\Common\Domain;

final class DomainEventPublisher
{
    /**
     * @var DomainEventSubscriber[]
     */
    private $subscribers;

    /**
     * DomainEventPublisher constructor.
     */
    public function __construct()
    {
        $this->subscribers = [];
    }

    /**
     * Add subscriber.
     *
     * @param DomainEventSubscriber $subscriber
     */
    public function subscribe(DomainEventSubscriber $subscriber): void
    {
        $this->subscribers[] = $subscriber;
    }

    /**
     * Publish the given domain events.
     *
     * @param DomainEvent[] $domainEvents
     */
    public function publish(array $domainEvents): void
    {
        foreach ($domainEvents as $domainEvent) {
            $this->publishSingle($domainEvent);
        }
    }

    /**
     * Publish the given domain event.
     *
     * @param DomainEvent $domainEvent
     */
    private function publishSingle(DomainEvent $domainEvent): void
    {
        foreach ($this->subscribers as $subscriber) {
            if ($subscriber->isSubscribedTo($domainEvent)) {
                $subscriber->handle($domainEvent);
            }
        }
    }
}
