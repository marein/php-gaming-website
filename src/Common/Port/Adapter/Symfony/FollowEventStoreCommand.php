<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Symfony;

use Gaming\Common\EventStore\CompositeStoredEventSubscriber;
use Gaming\Common\EventStore\EventStore;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\Common\ForkControl\ForkControl;
use Gaming\Common\ForkControl\Queue\StreamQueuePairFactory;
use Gaming\Common\Port\Adapter\Symfony\EventStorePointerFactory\EventStorePointerFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Traversable;

final class FollowEventStoreCommand extends Command
{
    /**
     * @param Traversable<string, StoredEventSubscriber> $storedEventSubscribers
     */
    public function __construct(
        private readonly EventStore $eventStore,
        private readonly EventStorePointerFactory $eventStorePointerFactory,
        private readonly iterable $storedEventSubscribers
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
            ->addOption(
                'subscriber',
                's',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'List of subscribers.'
            )
            ->addOption(
                'select-all-subscribers',
                null,
                InputOption::VALUE_NONE,
                'Overwrites individual subscribers. This is useful for the development environment.'
            )
            ->addOption(
                'throttle',
                't',
                InputOption::VALUE_OPTIONAL,
                'Throttle time after an empty run in milliseconds.',
                200
            )
            ->addOption(
                'batch',
                'b',
                InputOption::VALUE_OPTIONAL,
                'Batch size per run.',
                1000
            )
            ->addOption(
                'worker',
                'w',
                InputOption::VALUE_OPTIONAL,
                'Number of workers.',
                3
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $selectedSubscribers = $this->selectedSubscriberNames($input);
        if (count($selectedSubscribers) === 0) {
            $output->writeln(
                sprintf(
                    'Please select one of the following subscribers:%s* %s',
                    PHP_EOL,
                    implode(PHP_EOL . '* ', $this->availableSubscriberNames())
                )
            );
            return Command::FAILURE;
        }

        $unknownSubscriberNames = $this->unknownSubscriberNames($input);
        if (count($unknownSubscriberNames) !== 0) {
            $output->writeln(
                sprintf(
                    'The following subscribers are unknown:%s* %s',
                    PHP_EOL,
                    implode(PHP_EOL . '* ', $unknownSubscriberNames)
                )
            );
            return Command::FAILURE;
        }

        $forkControl = new ForkControl(
            new StreamQueuePairFactory()
        );
        $forkControl->fork(
            new Publisher(
                array_map(
                    fn() => $forkControl->fork(
                        new Worker($this->storedEventSubscriber($input))
                    ),
                    range(1, max(1, (int)$input->getOption('worker')))
                ),
                $this->eventStore,
                $this->eventStorePointerFactory,
                (string)$input->getArgument('pointer'),
                max(1, (int)$input->getOption('throttle')) * 1000,
                (int)$input->getOption('batch')
            )
        );
        $forkControl->wait();

        return Command::SUCCESS;
    }

    /**
     * @return string[]
     */
    private function selectedSubscriberNames(InputInterface $input): array
    {
        return $input->getOption('select-all-subscribers') ?
            $this->availableSubscriberNames() :
            $input->getOption('subscriber');
    }

    /**
     * @return string[]
     */
    private function availableSubscriberNames(): array
    {
        return array_keys(iterator_to_array($this->storedEventSubscribers));
    }

    /**
     * @return string[]
     */
    private function unknownSubscriberNames(InputInterface $input): array
    {
        return array_keys(
            array_diff_key(
                array_flip($this->selectedSubscriberNames($input)),
                iterator_to_array($this->storedEventSubscribers)
            )
        );
    }

    private function storedEventSubscriber(InputInterface $input): StoredEventSubscriber
    {
        return new CompositeStoredEventSubscriber(
            array_intersect_key(
                iterator_to_array($this->storedEventSubscribers),
                array_flip($this->selectedSubscriberNames($input))
            )
        );
    }
}
