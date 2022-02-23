<?php

declare(strict_types=1);

namespace Gaming\Common\ForkControl\Queue;

use Gaming\Common\ForkControl\Exception\ForkControlException;

interface Queue
{
    /**
     * @throws ForkControlException
     */
    public function send(mixed $message): void;

    /**
     * @throws ForkControlException
     */
    public function receive(): mixed;
}
