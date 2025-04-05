<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\Integration\Doctrine\EventListener;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Gaming\Common\EventStore\CollectsDomainEvents;
use Gaming\Common\EventStore\Integration\Doctrine\DoctrineEventStore;

final class AppendDomainEvents
{
    public function __construct(
        private readonly DoctrineEventStore $eventStore
    ) {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->appendDomainEvents($args->getObject(), $args->getObjectManager()->getConnection());
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $this->appendDomainEvents($args->getObject(), $args->getObjectManager()->getConnection());
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $this->appendDomainEvents($args->getObject(), $args->getObjectManager()->getConnection());
    }

    private function appendDomainEvents(object $object, Connection $connection): void
    {
        if ($object instanceof CollectsDomainEvents) {
            $this->eventStore->withConnection($connection)->append(...$object->flushDomainEvents());
        }
    }
}
