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
     * @throws ForkPoolException
     */
    public function receive(): mixed;
}
