<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

interface CollectsDomainEvents
{
    /**
     * @return DomainEvent[]
     */
    public function flushDomainEvents(): array;
}
