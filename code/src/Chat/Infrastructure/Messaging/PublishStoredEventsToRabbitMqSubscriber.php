<?php

namespace Gambling\Chat\Infrastructure\Messaging;

use Gambling\Common\EventStore\StoredEvent;
use Gambling\Common\EventStore\StoredEventSubscriber;
use Gambling\Common\MessageBroker\MessageBroker;

final class PublishStoredEventsToRabbitMqSubscriber implements StoredEventSubscriber
{
    /**
     * @var MessageBroker
     */
    private $messageBroker;

    /**
     * PublishStoredEventsToRabbitMqSubscriber constructor.
     *
     * @param MessageBroker $messageBroker
     */
    public function __construct(MessageBroker $messageBroker)
    {
        $this->messageBroker = $messageBroker;
    }

    /**
     * @inheritdoc
     */
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
            $storedEvent->payload(),
            'Chat.' . $storedEvent->name()
        );
    }

    /**
     * @inheritdoc
     */
    public function isSubscribedTo(StoredEvent $storedEvent): bool
    {
        return true;
    }
}
