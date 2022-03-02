<?php

declare(strict_types=1);

namespace Gaming\Common\ForkPool;

use Gaming\Common\ForkPool\Channel\Channel;

interface Task
{
    public function execute(Channel $channel): int;
}
