<?php

declare(strict_types=1);

namespace Gaming\Common\Timer\Integration;

use Gaming\Common\ForkPool\Channel\Channel;
use Gaming\Common\ForkPool\Channel\Channels;
use Gaming\Common\ForkPool\Channel\StreamChannelPairFactory;
use Gaming\Common\ForkPool\ClosureTask;
use Gaming\Common\ForkPool\ForkPool;
use Gaming\Common\ForkPool\Integration\Prometheus\ExposeMetricsTask;
use Gaming\Common\Timer\CancellationToken;
use Gaming\Common\Timer\TimeoutService;
use Prometheus\RegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This is a template to create commands that handle timeouts.
 * It parallelizes the handling of timeouts and exposes metrics.
 * This scales vertically by increasing the parallelism to minimize complexity.
 * Horizontal scaling would require distributed locks, queues or other synchronization mechanisms.
 */
abstract class HandleTimeoutsCommandTemplate extends Command
{
    public function __construct(
        private readonly TimeoutService $timeoutService,
        private readonly RegistryInterface $prometheusRegistry,
        private readonly string $metricsNamespace,
        private readonly ExposeMetricsTask $exposeMetricsTask
    ) {
        parent::__construct();
    }

    abstract protected function handleTimeout(string $timeoutId): void;

    public function configure()
    {
        $this
            ->addOption(
                'parallelism',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Defines how many timeouts can be processed in parallel.',
                1
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $parallelism = max(1, (int)$input->getOption('parallelism'));

        $forkPool = new ForkPool(new StreamChannelPairFactory());

        $workerChannels = new Channels(
            array_map(
                fn(): Channel => $forkPool->fork(
                    new ClosureTask(function (Channel $channel): int {
                        while ($message = $channel->receive()) {
                            if ($message === Channel::MESSAGE_SYNC) {
                                $channel->send(Channel::MESSAGE_SYNC_ACK);
                                continue;
                            }

                            $this->handleTimeout((string)$message);

                            $this->prometheusRegistry->getOrRegisterCounter(
                                $this->metricsNamespace,
                                'handled_timeouts_total',
                                'Total number of handled timeouts.'
                            )->inc();
                        }

                        return 0;
                    })
                )->channel(),
                range(1, $parallelism)
            )
        );

        $coordinator = $forkPool
            ->fork(
                new ClosureTask(function (Channel $channel) use ($workerChannels): int {
                    $cancellationToken = new CancellationToken();
                    pcntl_async_signals(true);
                    foreach ([SIGINT, SIGTERM] as $signal) {
                        pcntl_signal($signal, static fn() => $cancellationToken->cancel());
                    }

                    $this->timeoutService->listen(
                        static function (array $timeoutIds) use ($workerChannels): void {
                            foreach ($timeoutIds as $timeoutId) {
                                $workerChannels->roundRobin()->send($timeoutId);
                            }

                            $workerChannels->synchronize();
                        },
                        cancellationToken: $cancellationToken
                    );

                    return 0;
                })
            );

        $forkPool->fork($this->exposeMetricsTask);

        $forkPool->signal()
            ->enableAsyncDispatch()
            ->on(
                [SIGINT, SIGTERM],
                static function () use ($forkPool, $coordinator): void {
                    $coordinator->kill(SIGTERM);
                    $forkPool->wait()->killAllWhenAnyExits(SIGTERM);

                    exit(0);
                },
                false
            );

        $forkPool->wait()->killAllWhenAnyExits(SIGTERM);

        return Command::FAILURE;
    }
}
