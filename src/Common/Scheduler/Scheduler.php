<?php

declare(strict_types=1);

namespace Gaming\Common\Scheduler;

interface Scheduler
{
    public function schedule(int $invokeAt, Handler $handler): void;
}
