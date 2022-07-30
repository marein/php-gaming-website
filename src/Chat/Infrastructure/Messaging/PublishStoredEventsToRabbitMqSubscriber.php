<?php

declare(strict_types=1);

namespace Gaming\Chat\Infrastructure\Messaging;

use Gaming\Chat\Application\Event\ChatInitiated;
use Gaming\Chat\Application\Event\MessageWritten;
use Gaming\Common\Domain\DomainEvent;
use Gaming\Common\EventStore\NoCommit;
use Gaming\Common\EventStore\StoredEvent;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\Common\MessageBroker\MessageBroker;
use Gaming\Common\MessageBroker\Model\Message\Message;
use Gaming\Common\MessageBroker\Model\Message\Name;
use Gaming\Common\Normalizer\Normalizer;
use RuntimeException;

final class PublishStoredEventsToRabbitMqSubscriber implements StoredEventSubscriber
{
    use NoCommit;

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
                new Name('Chat', $this->nameFromDomainEvent($domainEvent)),
                json_encode(
                    $this->normalizer->normalize($domainEvent, $domainEvent::class),
                    JSON_THROW_ON_ERROR
                )
            )
        );
    }

    private function nameFromDomainEvent(DomainEvent $domainEvent): string
    {
        return match ($domainEvent::class) {
            ChatInitiated::class => 'ChatInitiated',
            MessageWritten::class => 'MessageWritten',
            default => throw new RuntimeException($domainEvent::class . ' must be handled.')
        };
    }
}
