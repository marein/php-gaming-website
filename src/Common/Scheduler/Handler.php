<?php

declare(strict_types=1);

namespace Gaming\Common\Scheduler;

interface Handler
{
    public function handle(Scheduler $scheduler): void;
}
