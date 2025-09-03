<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\Integration\Prometheus;

use DateTimeImmutable;
use DateTimeInterface;
use Gaming\Common\EventStore\Event\EventsCommitted;
use Prometheus\RegistryInterface;

final class CollectMetricsListener
{
    public function __construct(
        private readonly RegistryInterface $registry,
        private readonly string $metricsNamespace,
        private readonly string $recordedAtHeader = 'recordedAt',
        private readonly string $recordedAtFormat = DateTimeInterface::ATOM
    ) {
    }

    public function eventsCommitted(EventsCommitted $event): void
    {
        $this->registry->getOrRegisterCounter(
            $this->metricsNamespace,
            'event_store_commits_total',
            'Total number of committed events.'
        )->incBy(count($event->storedEvents));

        $lag = 0;
        if (count($event->storedEvents) > 0) {
            $lastDomainEvent = $event->storedEvents[count($event->storedEvents) - 1]->domainEvent();
            $recordedAt = DateTimeImmutable::createFromFormat(
                $this->recordedAtFormat,
                (string)($lastDomainEvent->headers[$this->recordedAtHeader] ?? '')
            );
            $lag = $recordedAt !== false ? time() - $recordedAt->getTimestamp() : 0;
        }

        $this->registry->getOrRegisterGauge(
            $this->metricsNamespace,
            'event_store_lag_seconds',
            'Lag in seconds since the last committed event.'
        )->set($lag);
    }
}
