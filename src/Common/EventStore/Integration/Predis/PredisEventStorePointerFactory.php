<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\Integration\Predis;

use Gaming\Common\EventStore\EventStorePointer;
use Gaming\Common\EventStore\EventStorePointerFactory;
use Gaming\Common\EventStore\InMemoryCacheEventStorePointer;
use Predis\ClientInterface;

final class PredisEventStorePointerFactory implements EventStorePointerFactory
{
    public function __construct(
        private readonly ClientInterface $predis
    ) {
    }

    public function withName(string $name): EventStorePointer
    {
        return new InMemoryCacheEventStorePointer(
            new PredisEventStorePointer(
                $this->predis,
                $name
            )
        );
    }
}
