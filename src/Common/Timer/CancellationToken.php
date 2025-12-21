<?php

declare(strict_types=1);

namespace Gaming\Common\Timer;

final class CancellationToken
{
    public private(set) bool $isCancelled = false;

    public function cancel(): void
    {
        $this->isCancelled = true;
    }
}
