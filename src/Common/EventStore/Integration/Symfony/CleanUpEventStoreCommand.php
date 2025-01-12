<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\Integration\Symfony;

use Gaming\Common\EventStore\CleanableEventStore;
use Gaming\Common\EventStore\EventStorePointerFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * If there are multiple streaming processes, this command can be used to safely
 * clean up the EventStore based on the given EventStorePointers.
 */
final class CleanUpEventStoreCommand extends Command
{
    public function __construct(
        private readonly CleanableEventStore $cleanableEventStore,
        private readonly EventStorePointerFactory $eventStorePointerFactory
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'pointers',
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'List of event store pointers.'
            )
            ->addOption(
                'keep-events',
                'k',
                InputOption::VALUE_OPTIONAL,
                'Defines how many events should be kept for safety.
                <comment>Please keep in mind that the id isn\'t gapless in some implementations.</comment>'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var non-empty-array<string, int> $pointers */
        $pointers = [];
        foreach ($input->getArgument('pointers') as $pointer) {
            $pointers[$pointer] = $this->eventStorePointerFactory
                ->withName($pointer)
                ->retrieveMostRecentPublishedStoredEventId();
        }
        $smallestPointerValue = max(min($pointers) - (int)$input->getOption('keep-events'), 0);

        $io->writeln('Fetched pointers:');
        $io->table(
            ['Pointer', 'Value'],
            array_map(static fn($pointer, $value): array => [$pointer, $value], array_keys($pointers), $pointers)
        );

        $this->cleanableEventStore->cleanUpTo($smallestPointerValue);

        $io->success('EventStore cleaned up to id "' . $smallestPointerValue . '".');

        return Command::SUCCESS;
    }
}
