<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

interface StoredEventSubscriber extends DomainEventSubscriber
{
    public function commit(): void;
}
