<?php
declare(strict_types=1);

namespace Gaming\Common\Domain;

final class ExtractPayloadForEventSubscriber implements DomainEventSubscriber
{
    /**
     * @var array|null
     */
    private $payload;

    /**
     * @var string
     */
    private $eventName;

    /**
     * ExtractPayloadSubscriber constructor.
     *
     * @param string $eventName
     */
    public function __construct(string $eventName)
    {
        $this->eventName = $eventName;
    }

    /**
     * @inheritdoc
     */
    public function handle(DomainEvent $domainEvent): void
    {
        $this->payload = $domainEvent->payload();
    }

    /**
     * @inheritdoc
     */
    public function isSubscribedTo(DomainEvent $domainEvent): bool
    {
        return $domainEvent->name() === $this->eventName;
    }

    /**
     * @return array|null
     */
    public function payload(): ?array
    {
        return $this->payload;
    }
}
