<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

interface EventStorePointerFactory
{
    public function withName(string $name): EventStorePointer;
}
