<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Symfony;

use Gaming\Common\EventStore\ConsistentOrderEventStore;
use Gaming\Common\EventStore\EventStore;
use Gaming\Common\EventStore\FollowEventStoreDispatcher;
use Gaming\Common\EventStore\InMemoryCacheEventStorePointer;
use Gaming\Common\EventStore\StoredEventPublisher;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\Common\EventStore\ThrottlingEventStore;
use Gaming\Common\Normalizer\Normalizer;
use Gaming\Common\Port\Adapter\EventStore\Subscriber\SymfonyConsoleDebugSubscriber;
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
        private readonly iterable $storedEventSubscribers,
        private readonly Normalizer $normalizer
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
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $availableStoredEventSubscribers = iterator_to_array($this->storedEventSubscribers);

        $selectedSubscriberNames = $input->getOption('subscriber');
        if ($input->getOption('select-all-subscribers')) {
            $selectedSubscriberNames = array_keys($availableStoredEventSubscribers);
        }

        $storedEventPublisher = new StoredEventPublisher();
        foreach ($selectedSubscriberNames as $selectedSubscriberName) {
            if (!array_key_exists($selectedSubscriberName, $availableStoredEventSubscribers)) {
                $output->writeln('Subscriber "' . $selectedSubscriberName . '" not known.');
                return Command::FAILURE;
            }
            $storedEventPublisher->subscribe($availableStoredEventSubscribers[$selectedSubscriberName]);
        }
        $storedEventPublisher->subscribe(new SymfonyConsoleDebugSubscriber($output, $this->normalizer));

        $followEventStoreDispatcher = new FollowEventStoreDispatcher(
            new InMemoryCacheEventStorePointer(
                $this->eventStorePointerFactory->withName(
                    (string)$input->getArgument('pointer')
                )
            ),
            new ThrottlingEventStore(
                new ConsistentOrderEventStore($this->eventStore),
                (int)$input->getOption('throttle')
            ),
            $storedEventPublisher
        );

        while (true) {
            $followEventStoreDispatcher->dispatch((int)$input->getOption('batch'));
        }
    }
}
