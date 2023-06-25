<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker;

final class Message
{
    /**
     * @param string $streamId is used by some implementations to colocate messages,
     *                         which in turn can help with caching, race conditions, ordering, etc.
     */
    public function __construct(
        private readonly string $name,
        private readonly string $body,
        private readonly string $streamId = ''
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

    public function streamId(): string
    {
        return $this->streamId;
    }

    /**
     * @return array{name: string, body: string, streamId: string}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'body' => $this->body,
            'streamId' => $this->streamId
        ];
    }
}
