<?php
declare(strict_types=1);

namespace Gaming\Common\Domain;

/**
 * @property-read array|null $payload
 * The payload of the given event name when handled, null otherwise.
 */
final class ExtractPayloadForEventSubscriber implements DomainEventSubscriber
{
    private const READONLY_PROPERTIES = ['payload'];

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
     * Accessor for private readonly properties.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        if (in_array($name, self::READONLY_PROPERTIES)) {
            return $this->$name;
        }

        // todo: Trigger error if not in array.
    }
}
