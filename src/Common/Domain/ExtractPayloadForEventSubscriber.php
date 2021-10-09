<?php

declare(strict_types=1);

namespace Gaming\Common\Domain;

final class ExtractPayloadForEventSubscriber implements DomainEventSubscriber
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $payload;

    private string $eventName;

    public function __construct(string $eventName)
    {
        $this->payload = null;
        $this->eventName = $eventName;
    }

    public function handle(DomainEvent $domainEvent): void
    {
        $this->payload = $domainEvent->payload();
    }

    public function isSubscribedTo(DomainEvent $domainEvent): bool
    {
        return $domainEvent->name() === $this->eventName;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function payload(): ?array
    {
        return $this->payload;
    }
}
