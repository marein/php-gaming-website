<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Event;

trait Stoppable
{
    private bool $isPropagationStopped = false;

    public function stopPropagation(): void
    {
        $this->isPropagationStopped = true;
    }

    public function isPropagationStopped(): bool
    {
        return $this->isPropagationStopped;
    }
}
