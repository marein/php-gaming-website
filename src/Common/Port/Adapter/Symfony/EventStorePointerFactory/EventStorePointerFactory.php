<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Symfony\EventStorePointerFactory;

use Gaming\Common\EventStore\EventStorePointer;

interface EventStorePointerFactory
{
    public function withName(string $name): EventStorePointer;
}
