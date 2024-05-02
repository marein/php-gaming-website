<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

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
}
