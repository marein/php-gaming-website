<?php

namespace Gambling\User\Port\Adapter\Persistence\EventStore;

use Gambling\Common\Domain\DomainEvent;
use Gambling\Common\Domain\DomainEventSubscriber;
use Gambling\Common\EventStore\EventStore;

final class StoreDomainEventsSubscriber implements DomainEventSubscriber
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * StoreEventsListener constructor.
     *
     * @param EventStore $eventStore
     */
    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    /**
     * @inheritdoc
     */
    public function handle(DomainEvent $domainEvent): void
    {
        $this->eventStore->append($domainEvent);
    }

    /**
     * @inheritdoc
     */
    public function isSubscribedTo(DomainEvent $domainEvent): bool
    {
        return true;
    }
}
