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

    /**
     * @param Closure(list<string>): void $handler
     */
    public function listen(
        Closure $handler,
        int $lookaheadWindowMs = 3000,
        int $maxSleepMs = 250,
        CancellationToken $cancellationToken = new CancellationToken()
    ): void {
        while (!$cancellationToken->isCancelled) {
            $nowMs = (int)(microtime(true) * 1000);
            $estimatedSleepTimeUs = $maxSleepMs * 1000;

            $timeouts = $this->timeoutStore->find($nowMs + $lookaheadWindowMs);
            $affectedTimeoutIds = [];
            foreach ($timeouts as $timeoutId => $handleAt) {
                $timeoutInMs = max(0, $handleAt - $nowMs);
                $estimatedSleepTimeUs = min($estimatedSleepTimeUs, $timeoutInMs * 1000);

                if ($timeoutInMs === 0) {
                    $affectedTimeoutIds[] = $timeoutId;
                }
            }

            $nowMsBeforeHandling = (int)(microtime(true) * 1000);
            $handler($affectedTimeoutIds);
            $this->timeoutStore->remove($affectedTimeoutIds);
            $estimatedSleepTimeUs -= (int)(microtime(true) * 1000 - $nowMsBeforeHandling) * 1000;

            usleep(min($maxSleepMs * 1000, max(0, $estimatedSleepTimeUs)));
        }
    }
}
