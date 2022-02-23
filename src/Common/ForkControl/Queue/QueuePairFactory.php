<?php

declare(strict_types=1);

namespace Gaming\Common\ForkControl\Queue;

use Gaming\Common\ForkControl\Exception\ForkControlException;

interface QueuePairFactory
{
    /**
     * @throws ForkControlException
     */
    public function create(): QueuePair;
}
