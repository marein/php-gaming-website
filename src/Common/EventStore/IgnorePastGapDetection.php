<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

use DateTimeImmutable;
use DateTimeInterface;
use Psr\Clock\ClockInterface;
use Throwable;

final class IgnorePastGapDetection implements GapDetection
{
    public function __construct(
        private readonly ClockInterface $clock,
        private readonly int $thresholdInSeconds = 10,
        private readonly ?GapDetection $nextGapResolver = null,
        private readonly string $recordedAtHeader = 'recordedAt',
        private readonly string $recordedAtFormat = DateTimeInterface::ATOM
    ) {
    }

    /**
     * @throws Throwable
     */
    public function shouldWaitForStoredEventWithId(int $expectedId, StoredEvent $actualEvent): bool
    {
        $headers = $actualEvent->domainEvent()->headers;
        if (!array_key_exists($this->recordedAtHeader, $headers)) {
            return $this->delegateToNextGapResolver($expectedId, $actualEvent);
        }

        $recordedAt = DateTimeImmutable::createFromFormat($this->recordedAtFormat, $headers[$this->recordedAtHeader]);
        if ($recordedAt === false) {
            return $this->delegateToNextGapResolver($expectedId, $actualEvent);
        }

        return match (true) {
            ($this->clock->now()->getTimestamp() - $recordedAt->getTimestamp()) <= $this->thresholdInSeconds => true,
            default => $this->delegateToNextGapResolver($expectedId, $actualEvent),
        };
    }

    private function delegateToNextGapResolver(int $expectedId, StoredEvent $actualEvent): bool
    {
        return $this->nextGapResolver?->shouldWaitForStoredEventWithId($expectedId, $actualEvent) ?? false;
    }
}
