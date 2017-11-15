<?php

namespace Gambling\Chat\Model;

use Gambling\Common\Domain\DomainEvent;

final class GenericDomainEvent implements DomainEvent
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $payload;

    /**
     * @var \DateTimeImmutable
     */
    private $occurredOn;

    /**
     * GenericDomainEvent constructor.
     *
     * @param string $name
     * @param array  $payload
     */
    public function __construct(string $name, array $payload)
    {
        $this->name = $name;
        $this->payload = $payload;
        $this->occurredOn = new \DateTimeImmutable();
    }

    /**
     * @inheritdoc
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }

    /**
     * @inheritdoc
     */
    public function payload(): array
    {
        return $this->payload;
    }
}
