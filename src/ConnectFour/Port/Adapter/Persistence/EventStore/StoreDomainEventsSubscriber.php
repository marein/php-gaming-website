<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\EventStore;

use Gaming\Common\Domain\DomainEvent;
use Gaming\Common\Domain\DomainEventSubscriber;
use Gaming\Common\EventStore\EventStore;

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
