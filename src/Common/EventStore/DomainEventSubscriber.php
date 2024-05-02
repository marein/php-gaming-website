<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

interface DomainEventSubscriber
{
    public function handle(DomainEvent $domainEvent): void;
}
