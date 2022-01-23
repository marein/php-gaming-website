<?php

declare(strict_types=1);

namespace Gaming\Identity\Port\Adapter\Messaging;

use Gaming\Common\EventStore\StoredEvent;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\Common\MessageBroker\MessageBroker;
use Gaming\Common\MessageBroker\Model\Message\Message;
use Gaming\Common\MessageBroker\Model\Message\Name;
use Gaming\Common\Normalizer\Normalizer;

final class PublishStoredEventsToRabbitMqSubscriber implements StoredEventSubscriber
{
    public function __construct(
        private readonly MessageBroker $messageBroker,
        private readonly Normalizer $normalizer
    ) {
    }

    public function handle(StoredEvent $storedEvent): void
    {
        $domainEvent = $storedEvent->domainEvent();

        // We should definitely filter the events we are going to publish,
        // since that belongs to our public interface for the other contexts.
        // However, it's not done for simplicity in this sample project.
        // We could
        //     * publish specific messages by name.
        //     * filter out specific properties in the payload.
        //     * translate when the properties for an event in the payload changed.
        //
        // We could use a strong message format like json schema, protobuf etc. to have
        // a clearly defined interface with other domains.
        $this->messageBroker->publish(
            new Message(
                new Name('Identity', $domainEvent->name()),
                json_encode(
                    $this->normalizer->normalize($domainEvent, $domainEvent::class),
                    JSON_THROW_ON_ERROR
                )
            )
        );
    }

    public function isSubscribedTo(StoredEvent $storedEvent): bool
    {
        return true;
    }
}
