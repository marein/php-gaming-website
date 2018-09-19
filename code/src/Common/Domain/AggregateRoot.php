<?php
declare(strict_types=1);

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
