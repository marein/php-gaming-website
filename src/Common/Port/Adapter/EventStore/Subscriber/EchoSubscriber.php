<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\EventStore\Subscriber;

use Gaming\Common\Domain\DomainEvent;
use Gaming\Common\EventStore\StoredEvent;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\Common\Normalizer\Normalizer;

final class EchoSubscriber implements StoredEventSubscriber
{
    public function __construct(
        private readonly Normalizer $normalizer
    ) {
    }

    public function handle(StoredEvent $storedEvent): void
    {
        $domainEvent = $storedEvent->domainEvent();

        echo sprintf(
            'Received #%s "%s" with "%s"' . PHP_EOL,
            $storedEvent->id(),
            $domainEvent::class,
            json_encode(
                $this->normalizer->normalize($domainEvent, DomainEvent::class),
                JSON_THROW_ON_ERROR
            )
        );
    }
}
