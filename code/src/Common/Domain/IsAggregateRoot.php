<?php
declare(strict_types=1);

namespace Gambling\Common\Domain;

trait IsAggregateRoot
{
    /**
     * @var DomainEvent[]
     */
    private $domainEvents = [];

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
