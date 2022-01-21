<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Symfony\EventStorePointerFactory;

use Gaming\Common\EventStore\EventStorePointer;
use Gaming\Common\Port\Adapter\EventStore\PredisEventStorePointer;
use Predis\ClientInterface;

final class PredisEventStorePointerFactory implements EventStorePointerFactory
{
    public function __construct(
        private readonly ClientInterface $predis
    ) {
    }

    public function withName(string $name): EventStorePointer
    {
        return new PredisEventStorePointer(
            $this->predis,
            $name
        );
    }
}
