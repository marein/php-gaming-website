<?php

declare(strict_types=1);

namespace Gaming\Common\ForkPool;

use Closure;
use Gaming\Common\ForkPool\Exception\ForkPoolException;

final class Signal
{
    public function __construct(
        private readonly ForkPool $forkPool
    ) {
    }

    /**
     * @param int[] $signals
     * @param Closure(int): void $handler
     *
     * @throws ForkPoolException
     */
    public function on(array $signals, Closure $handler, bool $restartSystemCalls): Signal
    {
        foreach ($signals as $signal) {
            if (!pcntl_signal($signal, $handler, $restartSystemCalls)) {
                throw new ForkPoolException(
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
     * @throws ForkPoolException
     */
    public function forwardSignalAndWait(array $signals): Signal
    {
        $this->on(
            $signals,
            function (int $signal): void {
                $this->forkPool->kill($signal)->wait()->all();

                exit(0);
            },
            false
        );

        return $this;
    }
}
