<?php

declare(strict_types=1);

namespace Gaming\Common\ForkControl;

use Closure;
use Gaming\Common\ForkControl\Exception\ForkControlException;

final class Signal
{
    public function __construct(
        private readonly ForkControl $forkControl
    ) {
    }

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

    /**
     * @param int[] $signals
     *
     * @throws ForkControlException
     */
    public function forwardSignalAndWait(array $signals): Signal
    {
        $this->on(
            $signals,
            function (int $signal): void {
                $this->forkControl->kill($signal)->wait()->all();

                exit(0);
            },
            false
        );

        return $this;
    }
}
