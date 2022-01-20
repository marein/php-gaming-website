<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Model\Message;

use Gaming\Common\MessageBroker\Model\Consumer\Name as ConsumerName;

final class Message
{
    private Name $name;

    private string $body;

    private ?ConsumerName $replyTo;

    public function __construct(
        Name $name,
        string $body,
        ?ConsumerName $replyTo = null
    ) {
        $this->name = $name;
        $this->body = $body;
        $this->replyTo = $replyTo;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function replyTo(): ?ConsumerName
    {
        return $this->replyTo;
    }
}
