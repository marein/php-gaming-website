<?php
declare(strict_types=1);

namespace Gaming\Common\Domain;

interface DomainEventSubscriber
{
    /**
     * Handle the event.
     *
     * @param DomainEvent $domainEvent
     */
    public function handle(DomainEvent $domainEvent): void;

    /**
     * Returns true if the subscriber handles the given domain event.
     *
     * @param DomainEvent $domainEvent
     *
     * @return bool
     */
    public function isSubscribedTo(DomainEvent $domainEvent): bool;
}
