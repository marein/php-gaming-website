<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\Event;

use Gaming\Common\EventStore\StoredEvent;

final class EventsFetched
{
    /**
     * @param StoredEvent[] $storedEvents
     */
    public function __construct(
        public readonly array $storedEvents
    ) {
    }
}
