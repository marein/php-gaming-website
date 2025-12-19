<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

use Throwable;

interface GapDetection
{
    /**
     * @throws Throwable
     */
    public function shouldWaitForStoredEventWithId(int $expectedId, StoredEvent $actualEvent): bool;
}
