<?php

declare(strict_types=1);

namespace Gaming\Identity\Port\Adapter\Messaging;

use Gaming\Common\EventStore\StoredEvent;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\Common\MessageBroker\MessageBroker;
use Gaming\Common\MessageBroker\Model\Message\Message;
use Gaming\Common\MessageBroker\Model\Message\Name;

final class PublishStoredEventsToRabbitMqSubscriber implements StoredEventSubscriber
{
    private MessageBroker $messageBroker;

    public function __construct(MessageBroker $messageBroker)
    {
        $this->messageBroker = $messageBroker;
    }

    public function handle(StoredEvent $storedEvent): void
    {
        // We should definitely filter the events we are going to publish,
        // since that belongs to our public interface for the other contexts.
        // However, it's not done for simplicity in this sample project.
        // We could
        //     * publish specific messages by name.
        //     * filter out specific properties in the payload.
        //     * translate when the properties for an event in the payload changed.
        $this->messageBroker->publish(
            new Message(
                new Name('Identity', $storedEvent->name()),
                $storedEvent->payload()
            )
        );
    }

    public function isSubscribedTo(StoredEvent $storedEvent): bool
    {
        return true;
    }
}
