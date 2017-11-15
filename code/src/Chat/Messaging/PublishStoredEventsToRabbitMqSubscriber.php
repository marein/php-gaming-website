<?php

namespace Gambling\Chat\Messaging;

use Gambling\Common\EventStore\StoredEvent;
use Gambling\Common\EventStore\StoredEventSubscriber;
use Gambling\Common\Port\Adapter\Messaging\MessageBroker;

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
        $this->messageBroker->publish(
            $storedEvent->payload(),
            $storedEvent->name()
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
