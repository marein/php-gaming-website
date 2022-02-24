<?php

declare(strict_types=1);

namespace Gaming\Common\ForkControl\Channel;

use Gaming\Common\ForkControl\Exception\ForkControlException;

interface Channel
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
