<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker;

final class Message
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        private readonly string $name,
        private readonly string $body,
        private readonly array $headers = []
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function body(): string
    {
        return $this->body;
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return $this->headers;
    }
}
