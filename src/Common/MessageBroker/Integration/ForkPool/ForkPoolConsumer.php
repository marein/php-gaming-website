<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\ForkPool;

use Gaming\Common\ForkPool\Channel\NullChannelPairFactory;
use Gaming\Common\ForkPool\ForkPool;
use Gaming\Common\ForkPool\Task;
use Gaming\Common\MessageBroker\Consumer;

final class ForkPoolConsumer implements Consumer
{
    private readonly ForkPool $forkPool;

    /**
     * @param iterable<Consumer> $consumers
     */
    public function __construct(
        private readonly iterable $consumers,
        private readonly ?Task $exposeMetricsTask = null
    ) {
        $this->forkPool = new ForkPool(new NullChannelPairFactory());
    }

    public function start(int $parallelism): void
    {
        foreach ($this->consumers as $consumer) {
            $this->forkPool->fork(new ConsumerTask($consumer, $parallelism));
        }

        if ($this->exposeMetricsTask !== null) {
            $this->forkPool->fork($this->exposeMetricsTask);
        }

        $this->forkPool->signal()->enableAsyncDispatch()->forwardSignalAndWait([SIGINT, SIGTERM]);
        $this->forkPool->wait()->killAllWhenAnyExits(SIGTERM);
        exit(1);
    }

    public function stop(): void
    {
        $this->forkPool->kill(SIGTERM)->wait()->all();
    }
}
