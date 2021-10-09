<?php

declare(strict_types=1);

namespace Gaming\Common\Domain;

final class DomainEventPublisher
{
    /**
     * @var DomainEventSubscriber[]
     */
    private array $subscribers;

    public function __construct()
    {
        $this->subscribers = [];
    }

    public function subscribe(DomainEventSubscriber $subscriber): void
    {
        $this->subscribers[] = $subscriber;
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
            if ($subscriber->isSubscribedTo($domainEvent)) {
                $subscriber->handle($domainEvent);
            }
        }
    }
}
