<?php

declare(strict_types=1);

namespace Gaming\Common\Domain;

interface AggregateRoot
{
    /**
     * Flush all stored domain events.
     *
     * @return DomainEvent[]
     */
    public function flushDomainEvents(): array;
}
