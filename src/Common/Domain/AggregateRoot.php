<?php

declare(strict_types=1);

namespace Gaming\Common\Domain;

interface AggregateRoot
{
    /**
     * @return DomainEvent[]
     */
    public function flushDomainEvents(): array;
}
