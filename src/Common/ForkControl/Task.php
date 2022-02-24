<?php

declare(strict_types=1);

namespace Gaming\Common\ForkControl;

use Gaming\Common\ForkControl\Queue\Queue;

interface Task
{
    public function execute(Queue $queue): int;
}
