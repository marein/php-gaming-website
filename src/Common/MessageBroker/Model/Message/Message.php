<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Model\Message;

final class Message
{
    private Name $name;

    private string $body;

    public function __construct(Name $name, string $body)
    {
        $this->name = $name;
        $this->body = $body;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function body(): string
    {
        return $this->body;
    }
}
