<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\EventStore;

use Gaming\Common\Domain\DomainEvent;
use Gaming\Common\Domain\DomainEventSubscriber;
use Gaming\Common\EventStore\EventStore;

final class StoreDomainEventsSubscriber implements DomainEventSubscriber
{
    private EventStore $eventStore;

    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    public function handle(DomainEvent $domainEvent): void
    {
        $this->eventStore->append($domainEvent);
    }
}
