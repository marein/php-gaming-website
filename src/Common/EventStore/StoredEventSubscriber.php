<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

use Gaming\Common\Domain\DomainEventSubscriber;

interface StoredEventSubscriber extends DomainEventSubscriber
{
    public function commit(): void;
}
