<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Model\Message;

final class Message
{
    /**
     * @var Name
     */
    private Name $name;

    /**
     * @var string
     */
    private string $body;

    /**
     * Message constructor.
     *
     * @param Name $name
     * @param string $body
     */
    public function __construct(Name $name, string $body)
    {
        $this->name = $name;
        $this->body = $body;
    }

    /**
     * Returns the name of the message.
     *
     * @return Name
     */
    public function name(): Name
    {
        return $this->name;
    }

    /**
     * Returns the body of the message.
     *
     * @return string
     */
    public function body(): string
    {
        return $this->body;
    }
}
