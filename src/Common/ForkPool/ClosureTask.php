<?php

declare(strict_types=1);

namespace Gaming\Common\ForkPool;

use Closure;
use Gaming\Common\ForkPool\Channel\Channel;

final class ClosureTask implements Task
{
    /**
     * @param Closure(Channel): int $task
     */
    public function __construct(
        private readonly Closure $task
    ) {
    }

    public function execute(Channel $channel): int
    {
        return ($this->task)($channel);
    }
}
