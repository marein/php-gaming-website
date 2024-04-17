<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

final class DomainEvents
{
    /**
     * @param DomainEvent[] $domainEvents
     */
    public function __construct(
        private readonly string $streamId,
        private int $streamVersion = 0,
        private array $domainEvents = []
    ) {
    }

    public function streamVersion(): int
    {
        return $this->streamVersion;
    }

    public function append(object ...$contents): self
    {
        foreach ($contents as $content) {
            $this->domainEvents[] = new DomainEvent($this->streamId, $content, ++$this->streamVersion);
        }

        return $this;
    }

    /**
     * @return DomainEvent[]
     */
    public function flush(): array
    {
        return array_splice($this->domainEvents, 0);
    }
}
