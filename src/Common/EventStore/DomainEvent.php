<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

final class DomainEvent
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        public readonly string $streamId,
        public readonly object $content,
        public readonly int $streamVersion = 0,
        public readonly array $headers = []
    ) {
    }

    public function withHeader(string $name, string $value): self
    {
        return new self(
            $this->streamId,
            $this->content,
            $this->streamVersion,
            array_merge($this->headers, [$name => $value])
        );
    }
}
