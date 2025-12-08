<?php

declare(strict_types=1);

namespace Gaming\Chat\Infrastructure\Messaging;

use DateTimeInterface;
use Gaming\Chat\Application\Event\MessageWritten;
use Gaming\Common\EventStore\DomainEvent;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\Common\MessageBroker\Message;
use Gaming\Common\MessageBroker\Publisher;
use GamingPlatform\Api\Chat\V1\ChatV1;

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
            MessageWritten::class => $this->handleMessageWritten($content),
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
                ChatV1::MessageWrittenType,
                ChatV1::createMessageWritten()
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
