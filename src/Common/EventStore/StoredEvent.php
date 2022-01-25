<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

use DateTimeImmutable;
use Gaming\Common\Domain\DomainEvent;

final class StoredEvent
{
    public function __construct(
        private readonly int $id,
        private readonly DateTimeImmutable $occurredOn,
        private readonly DomainEvent $domainEvent
    ) {
    }

    public function id(): int
    {
        return $this->id;
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function domainEvent(): DomainEvent
    {
        return $this->domainEvent;
    }
}
