<?php

declare(strict_types=1);

namespace Gaming\Common\Domain;

/**
 * @deprecated Since the EventStore is decoupled from this interface, domains don't need to implement it anymore.
 *             It only exists for compatibility reasons, so that the changes can happen gradually.
 */
interface DomainEvent
{
    public function aggregateId(): string;
}
