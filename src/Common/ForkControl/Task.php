<?php

declare(strict_types=1);

namespace Gaming\Common\ForkControl;

interface Task
{
    public function execute(Process $parent): int;
}
