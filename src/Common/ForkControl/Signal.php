<?php

declare(strict_types=1);

namespace Gaming\Common\ForkControl;

use Closure;
use Gaming\Common\ForkControl\Exception\ForkControlException;

final class Signal
{
    /**
     * @param int[] $signals
     * @param Closure(int): void $handler
     *
     * @throws ForkControlException
     */
    public function on(array $signals, Closure $handler, bool $restartSystemCalls): Signal
    {
        foreach ($signals as $signal) {
            if (!pcntl_signal($signal, $handler, $restartSystemCalls)) {
                throw new ForkControlException(
                    'Cannot register signal %s.',
                    $signal
                );
            }
        }

        return $this;
    }

    public function dispatchAsync(): Signal
    {
        pcntl_async_signals(true);

        return $this;
    }
}
