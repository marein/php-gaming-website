<?php
declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Message;

final class Message
{
    /**
     * @var Name
     */
    private $name;

    /**
     * @var string
     */
    private $body;

    /**
     * Message constructor.
     *
     * @param Name   $name
     * @param string $body
     */
    public function __construct(Name $name, string $body)
    {
        $this->name = $name;
        $this->body = $body;
    }

    /**
     * @return Name
     */
    public function name(): Name
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function body(): string
    {
        return $this->body;
    }
}
