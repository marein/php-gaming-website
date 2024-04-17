<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

final class InMemoryEventStore implements EventStore
{
    /**
     * @var array<string, DomainEvent[]>
     */
    private array $domainEvents = [];

    public function byStreamId(string $streamId, int $fromStreamVersion = 0): array
    {
        return array_values(
            array_filter(
                $this->domainEvents[$streamId] ?? [],
                static fn(DomainEvent $domainEvent): bool => $domainEvent->streamVersion >= $fromStreamVersion
            )
        );
    }

    public function append(DomainEvent ...$domainEvents): void
    {
        foreach ($domainEvents as $domainEvent) {
            $this->domainEvents[$domainEvent->streamId][] = $domainEvent;
        }
    }
}
