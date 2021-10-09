<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

use DateTimeImmutable;

final class StoredEvent
{
    private int $id;

    private string $name;

    private string $aggregateId;

    private string $payload;

    private DateTimeImmutable $occurredOn;

    public function __construct(
        int $id,
        string $name,
        string $aggregateId,
        string $payload,
        DateTimeImmutable $occurredOn
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->aggregateId = $aggregateId;
        $this->payload = $payload;
        $this->occurredOn = $occurredOn;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function aggregateId(): string
    {
        return $this->aggregateId;
    }

    public function payload(): string
    {
        return $this->payload;
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
