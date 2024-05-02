<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\Integration\Doctrine\EventListener;

use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Gaming\Common\EventStore\CollectsDomainEvents;
use Gaming\Common\EventStore\DomainEventSubscriber;

final class PublishDomainEvents
{
    public function __construct(
        private readonly DomainEventSubscriber $domainEventSubscriber
    ) {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->publishDomainEvents($args->getObject());
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $this->publishDomainEvents($args->getObject());
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $this->publishDomainEvents($args->getObject());
    }

    private function publishDomainEvents(object $object): void
    {
        if (!$object instanceof CollectsDomainEvents) {
            return;
        }

        foreach ($object->flushDomainEvents() as $domainEvent) {
            $this->domainEventSubscriber->handle($domainEvent);
        }
    }
}
