<?php

declare(strict_types=1);

namespace Gaming\Identity\Port\Adapter\Messaging;

use Gaming\Common\EventStore\DomainEvent;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\Common\MessageBroker\Message;
use Gaming\Common\MessageBroker\Publisher;
use Gaming\Identity\Domain\Model\User\Event\UserArrived;
use Gaming\Identity\Domain\Model\User\Event\UserSignedUp;

final class PublishDomainEventsToMessageBrokerSubscriber implements StoredEventSubscriber
{
    public function __construct(
        private readonly Publisher $publisher
    ) {
    }

    public function handle(DomainEvent $domainEvent): void
    {
        $content = $domainEvent->content;

        match ($content::class) {
            UserArrived::class => $this->handleUserArrived($content),
            UserSignedUp::class => $this->handleUserSignedUp($content),
            default => true
        };
    }

    public function commit(): void
    {
        $this->publisher->flush();
    }

    private function handleUserArrived(UserArrived $event): void
    {
        $this->publisher->send(
            new Message(
                'Identity.UserArrived',
                (new \GamingPlatform\Api\Identity\V1\UserArrived())
                    ->setUserId($event->aggregateId())
                    ->serializeToString(),
                $event->aggregateId()
            )
        );
    }

    private function handleUserSignedUp(UserSignedUp $event): void
    {
        $this->publisher->send(
            new Message(
                'Identity.UserSignedUp',
                (new \GamingPlatform\Api\Identity\V1\UserSignedUp())
                    ->setUserId($event->aggregateId())
                    ->setUsername($event->username())
                    ->serializeToString(),
                $event->aggregateId()
            )
        );
    }
}
