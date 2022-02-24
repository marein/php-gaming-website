<?php

declare(strict_types=1);

namespace Gaming\Common\ForkControl;

use Gaming\Common\ForkControl\Channel\Channel;

interface Task
{
    public function execute(Channel $channel): int;
}
