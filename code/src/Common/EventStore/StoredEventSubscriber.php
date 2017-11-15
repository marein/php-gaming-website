<?php

namespace Gambling\Common\EventStore;

interface StoredEventSubscriber
{
    /**
     * Handle the event.
     *
     * @param StoredEvent $storedEvent
     */
    public function handle(StoredEvent $storedEvent): void;

    /**
     * Returns true if the subscriber handles the given stored event.
     *
     * @param StoredEvent $storedEvent
     *
     * @return bool
     */
    public function isSubscribedTo(StoredEvent $storedEvent): bool;
}
