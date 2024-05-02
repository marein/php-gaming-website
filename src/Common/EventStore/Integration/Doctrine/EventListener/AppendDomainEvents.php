<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\Integration\Doctrine\EventListener;

use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Gaming\Common\EventStore\CollectsDomainEvents;
use Gaming\Common\EventStore\EventStore;

final class AppendDomainEvents
{
    public function __construct(
        private readonly EventStore $eventStore
    ) {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->appendDomainEvents($args->getObject());
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $this->appendDomainEvents($args->getObject());
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $this->appendDomainEvents($args->getObject());
    }

    private function appendDomainEvents(object $object): void
    {
        if ($object instanceof CollectsDomainEvents) {
            $this->eventStore->append(...$object->flushDomainEvents());
        }
    }
}
