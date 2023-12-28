<?php

declare(strict_types=1);

namespace Gaming\Common\ForkPool;

use Gaming\Common\ForkPool\Channel\Channel;

final class SessionLeaderTask implements Task
{
    public function __construct(
        private readonly Task $task
    ) {
    }

    public function execute(Channel $channel): int
    {
        posix_setsid();

        return $this->task->execute($channel);
    }
}
