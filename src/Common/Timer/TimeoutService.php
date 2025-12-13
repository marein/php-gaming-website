<?php

declare(strict_types=1);

namespace Gaming\Common\Timer;

use Closure;

final class TimeoutService
{
    public function __construct(
        private readonly TimeoutStore $timeoutStore
    ) {
    }

    /**
     * @param array<string, int> $timeouts The key is the timeout id and the value is the
     *                                     time in ms when the timeout should be handled.
     */
    public function add(array $timeouts): void
    {
        $this->timeoutStore->add($timeouts);
    }

    /**
     * @param list<string> $timeoutIds
     */
    public function remove(array $timeoutIds): void
    {
        $this->timeoutStore->remove($timeoutIds);
    }

    public function listen(
        Closure $handler,
        int $lookaheadWindowMs = 3000,
        int $maxSleepMs = 250,
        bool &$shouldRun = true
    ): void {
        while ($shouldRun) {
            $nowMs = (int)(microtime(true) * 1000);
            $estimatedSleepTime = $maxSleepMs * 1000;

            $timeouts = $this->timeoutStore->find($nowMs + $lookaheadWindowMs);
            $handledTimeoutIds = [];
            foreach ($timeouts as $timeoutId => $handleAt) {
                $timeoutIn = max(0, $handleAt - $nowMs);
                $estimatedSleepTime = min($estimatedSleepTime, $timeoutIn * 1000);

                if ($timeoutIn === 0) {
                    $handler($timeoutId);
                    $handledTimeoutIds[] = $timeoutId;
                }
            }
            $this->timeoutStore->remove($handledTimeoutIds);

            usleep(min($maxSleepMs * 1000, $estimatedSleepTime));
        }
    }
}
