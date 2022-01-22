<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

use DateTimeImmutable;
use Gaming\Common\Domain\DomainEvent;

final class StoredEvent
{
    public function __construct(
        private readonly int $id,
        private readonly DomainEvent $domainEvent
    ) {
    }

    public function id(): int
    {
        return $this->id;
    }

    public function domainEvent(): DomainEvent
    {
        return $this->domainEvent;
    }

    /**
     * @deprecated Use StoredEvent::domainEvent()->name() instead.
     */
    public function name(): string
    {
        return $this->domainEvent->name();
    }

    /**
     * @deprecated Use StoredEvent::domainEvent()->aggregateId() instead.
     */
    public function aggregateId(): string
    {
        return $this->domainEvent->aggregateId();
    }

    /**
     * @deprecated Use StoredEvent::domainEvent()->payload() instead.
     */
    public function payload(): string
    {
        return json_encode($this->domainEvent->payload(), JSON_THROW_ON_ERROR);
    }

    /**
     * @deprecated Use StoredEvent::domainEvent()->occurredOn() instead.
     */
    public function occurredOn(): DateTimeImmutable
    {
        return $this->domainEvent->occurredOn();
    }
}
