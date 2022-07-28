<?php

declare(strict_types=1);

namespace Gaming\Common\Scheduler;

final class NullHandler implements Handler
{
    public function handle(Scheduler $scheduler): void
    {
    }
}
