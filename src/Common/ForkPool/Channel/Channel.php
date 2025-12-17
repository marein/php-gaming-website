<?php

declare(strict_types=1);

namespace Gaming\Common\ForkPool\Channel;

use Gaming\Common\ForkPool\Exception\ForkPoolException;

interface Channel
{
    /**
     * @throws ForkPoolException
     */
    public function send(mixed $message): void;

    /**
     * @param int|null $timeout Timeout in seconds. 0 means no wait. Null means wait indefinitely.
     *
     * @return mixed What was received or null on timeout.
     * @throws ForkPoolException
     */
    public function receive(?int $timeout = null): mixed;
}
