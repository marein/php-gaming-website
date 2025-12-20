<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\Integration\ForkPool;

use Gaming\Common\EventStore\DomainEvent;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\Common\ForkPool\Channel\Channels;

final class ForwardToChannelStoredEventSubscriber implements StoredEventSubscriber
{
    public function __construct(
        private readonly Channels $channels
    ) {
    }

    public function handle(DomainEvent $domainEvent): void
    {
        $this->channels->consistent($domainEvent->streamId)->send($domainEvent);
    }

    public function commit(): void
    {
        $this->channels->synchronize();
    }
}
