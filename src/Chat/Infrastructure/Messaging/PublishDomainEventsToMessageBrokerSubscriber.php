<?php

declare(strict_types=1);

namespace Gaming\Chat\Infrastructure\Messaging;

use DateTimeInterface;
use Gaming\Chat\Application\Event\MessageWritten;
use Gaming\Common\Domain\DomainEvent;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\Common\MessageBroker\Message;
use Gaming\Common\MessageBroker\Publisher;

final class PublishDomainEventsToMessageBrokerSubscriber implements StoredEventSubscriber
{
    public function __construct(
        private readonly Publisher $publisher
    ) {
    }

    public function handle(DomainEvent $domainEvent): void
    {
        match ($domainEvent::class) {
            MessageWritten::class => $this->handleMessageWritten($domainEvent),
            default => true
        };
    }

    public function commit(): void
    {
        $this->publisher->flush();
    }

    private function handleMessageWritten(MessageWritten $event): void
    {
        $this->publisher->send(
            new Message(
                'Chat.MessageWritten',
                (new \GamingPlatform\Api\Chat\V1\MessageWritten())
                    ->setChatId($event->aggregateId())
                    ->setMessageId((string)$event->messageId())
                    ->setAuthorId($event->authorId())
                    ->setWrittenAt($event->writtenAt()->format(DateTimeInterface::ATOM))
                    ->setMessage($event->message())
                    ->serializeToString(),
                $event->aggregateId()
            )
        );
    }
}
