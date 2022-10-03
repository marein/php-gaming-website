<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

trait NoCommit
{
    public function commit(): void
    {
    }
}
