<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\Integration\Symfony;

use Gaming\Common\EventStore\Event\EventsCommitted;
use Symfony\Contracts\Service\ResetInterface;

final class ResetServicesListener
{
    private int $numberOfHandledCommits;

    public function __construct(
        private readonly ResetInterface $resettable,
        private readonly int $everyNthCommit
    ) {
        $this->numberOfHandledCommits = 0;
    }

    public function eventsCommitted(EventsCommitted $event): void
    {
        if (++$this->numberOfHandledCommits % $this->everyNthCommit === 0) {
            $this->resettable->reset();
        }
    }
}
