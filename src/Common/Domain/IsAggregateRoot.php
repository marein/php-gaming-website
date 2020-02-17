<?php
declare(strict_types=1);

namespace Gaming\Common\Domain;

trait IsAggregateRoot
{
    /**
     * @var DomainEvent[]
     */
    private array $domainEvents = [];

    /**
     * Flush all stored domain events.
     *
     * @return DomainEvent[]
     */
    public function flushDomainEvents(): array
    {
        $domainEvents = $this->domainEvents;

        $this->domainEvents = [];

        return $domainEvents;
    }
}
