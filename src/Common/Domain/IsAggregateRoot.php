<?php

declare(strict_types=1);

namespace Gaming\Common\Domain;

/**
 * @deprecated
 */
trait IsAggregateRoot
{
    /**
     * @var DomainEvent[]
     */
    private array $domainEvents = [];

    /**
     * @return DomainEvent[]
     */
    public function flushDomainEvents(): array
    {
        return array_splice($this->domainEvents, 0);
    }
}
