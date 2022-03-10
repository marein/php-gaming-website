<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

interface StoredEventSubscriber
{
    public function handle(StoredEvent $storedEvent): void;

    public function commit(): void;
}
