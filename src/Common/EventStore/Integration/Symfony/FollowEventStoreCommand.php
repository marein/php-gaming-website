<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\Integration\Symfony;

use Gaming\Common\EventStore\CompositeStoredEventSubscriber;
use Gaming\Common\EventStore\EventStorePointerFactory;
use Gaming\Common\EventStore\FollowEventStoreDispatcher;
use Gaming\Common\EventStore\Integration\ForkPool\ForwardToChannelStoredEventSubscriber;
use Gaming\Common\EventStore\Integration\ForkPool\Publisher;
use Gaming\Common\EventStore\Integration\ForkPool\Worker;
use Gaming\Common\EventStore\PollableEventStore;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\Common\ForkPool\Channel\StreamChannelPairFactory;
use Gaming\Common\ForkPool\ForkPool;
use Gaming\Common\ForkPool\Task;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Service\ServiceProviderInterface;

final class FollowEventStoreCommand extends Command
{
    /**
     * @param ServiceProviderInterface<StoredEventSubscriber> $storedEventSubscribers
     */
    public function __construct(
        private readonly PollableEventStore $pollableEventStore,
        private readonly EventStorePointerFactory $eventStorePointerFactory,
        private readonly ServiceProviderInterface $storedEventSubscribers,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ?Task $exposeMetricsTask = null,
        private readonly string $allSubscribersName = 'all'
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'pointer',
                InputArgument::REQUIRED,
                'Name of the event store pointer.'
            )
            ->addArgument(
                'subscribers',
                InputArgument::REQUIRED,
                'Comma separated list of subscribers.'
            )
            ->addOption(
                'throttle',
                't',
                InputOption::VALUE_OPTIONAL,
                'Defines throttle time after an empty batch in milliseconds.',
                200
            )
            ->addOption(
                'batch',
                'b',
                InputOption::VALUE_OPTIONAL,
                'Defines how many events are fetched per batch.',
                1000
            )
            ->addOption(
                'parallelism',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Defines how many events can be processed in parallel.',
                1
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $subscriberNames = explode(',', (string)$input->getArgument('subscribers'));
        $parallelism = max(1, (int)$input->getOption('parallelism'));

        $unknownSubscriberNames = $this->getUnknownSubscriberNames($subscriberNames);
        if (count($unknownSubscriberNames) !== 0) {
            $symfonyStyle->error(
                sprintf(
                    "The following subscribers don't exist:\n* %s\n\nAvailable subscribers:\n* %s\n* %s",
                    implode("\n* ", $unknownSubscriberNames),
                    $this->allSubscribersName . ' (ignores individual subscribers)',
                    implode("\n* ", array_keys($this->storedEventSubscribers->getProvidedServices()))
                )
            );
            return Command::FAILURE;
        }

        $symfonyStyle->success(
            sprintf(
                'Start subscribers "%s" with parallelism of %s.',
                implode('", "', $subscriberNames),
                $parallelism
            )
        );

        $forkPool = new ForkPool(new StreamChannelPairFactory());

        $publisher = $forkPool->fork(
            new Publisher(
                new FollowEventStoreDispatcher(
                    $this->pollableEventStore,
                    $this->eventStorePointerFactory->withName((string)$input->getArgument('pointer')),
                    new ForwardToChannelStoredEventSubscriber(
                        array_map(
                            fn() => $forkPool->fork(
                                new Worker($this->getStoredEventSubscriber($subscriberNames))
                            )->channel(),
                            range(1, $parallelism)
                        )
                    ),
                    max(1, (int)$input->getOption('batch')),
                    max(1, (int)$input->getOption('throttle')),
                    $this->eventDispatcher
                )
            )
        );

        if ($this->exposeMetricsTask !== null) {
            $forkPool->fork($this->exposeMetricsTask);
        }

        $forkPool->signal()
            ->enableAsyncDispatch()
            ->on(
                [SIGINT, SIGTERM],
                static function () use ($forkPool, $publisher): void {
                    $publisher->kill(SIGTERM);
                    $forkPool->wait()->killAllWhenAnyExits(SIGTERM);

                    exit(0);
                },
                false
            );

        $forkPool->wait()->killAllWhenAnyExits(SIGTERM);

        return Command::SUCCESS;
    }

    /**
     * @param string[] $names
     */
    private function getStoredEventSubscriber(array $names): StoredEventSubscriber
    {
        return new CompositeStoredEventSubscriber(
            array_map(
                fn(string $name): StoredEventSubscriber => $this->storedEventSubscribers->get($name),
                in_array($this->allSubscribersName, $names)
                    ? array_keys($this->storedEventSubscribers->getProvidedServices())
                    : $names
            )
        );
    }

    /**
     * @param string[] $subscriberNames
     *
     * @return string[]
     */
    private function getUnknownSubscriberNames(array $subscriberNames): array
    {
        return array_filter(
            $subscriberNames,
            fn(string $name): bool => !$this->storedEventSubscribers->has($name) && $name !== $this->allSubscribersName
        );
    }
}
