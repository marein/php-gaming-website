<?php

declare(strict_types=1);

namespace Gaming\Common\Scheduler;

final class Job
{
    public function __construct(
        public readonly int $invokeAt,
        public readonly Handler $handler
    ) {
    }
}
