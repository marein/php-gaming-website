<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Port\Adapter\Messaging;

use Gaming\Common\EventStore\DomainEvent;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\Common\MessageBroker\Message;
use Gaming\Common\MessageBroker\Publisher;
use Gaming\Common\Normalizer\Normalizer;
use Gaming\TicTacToe\Domain\Challenge\Event\ChallengeAccepted;
use Gaming\TicTacToe\Domain\Challenge\Event\ChallengeOpened;
use Gaming\TicTacToe\Domain\Challenge\Event\ChallengeWithdrawn;
use RuntimeException;

final class PublishDomainEventsToMessageBrokerSubscriber implements StoredEventSubscriber
{
    public function __construct(
        private readonly Publisher $publisher,
        private readonly Normalizer $normalizer
    ) {
    }

    public function handle(DomainEvent $domainEvent): void
    {
        $content = $domainEvent->content;

        $this->publisher->send(
            new Message(
                'TicTacToe.' . $this->nameFromDomainEvent($content),
                json_encode(
                    $this->normalizer->normalize($content, $content::class),
                    JSON_THROW_ON_ERROR
                ),
                $domainEvent->streamId
            )
        );
    }

    public function commit(): void
    {
        $this->publisher->flush();
    }

    private function nameFromDomainEvent(object $domainEvent): string
    {
        return match ($domainEvent::class) {
            ChallengeOpened::class => 'ChallengeOpened',
            ChallengeAccepted::class => 'ChallengeAccepted',
            ChallengeWithdrawn::class => 'ChallengeWithdrawn',
            default => throw new RuntimeException($domainEvent::class . ' must be handled.')
        };
    }
}
