<?php

declare(strict_types=1);

namespace Gaming\Common\ForkControl\Queue;

final class QueuePair
{
    public function __construct(
        private readonly Queue $parent,
        private readonly Queue $fork
    ) {
    }

    public function parent(): Queue
    {
        return $this->parent;
    }

    public function fork(): Queue
    {
        return $this->fork;
    }
}
