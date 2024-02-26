<?php

declare(strict_types=1);

namespace Gaming\Common\Domain\Integration\Doctrine;

use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Gaming\Common\Domain\AggregateRoot;
use Gaming\Common\Domain\DomainEventPublisher;

final class PublishDomainEventsListener
{
    public function __construct(
        private readonly DomainEventPublisher $domainEventPublisher
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
        if ($object instanceof AggregateRoot) {
            $this->domainEventPublisher->publish($object->flushDomainEvents());
        }
    }
}
