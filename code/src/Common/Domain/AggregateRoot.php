<?php

namespace Gambling\Common\Domain;

interface AggregateRoot
{
    /**
     * Flush all stored domain events.
     *
     * @return DomainEvent[]
     */
    public function flushDomainEvents(): array;
}
