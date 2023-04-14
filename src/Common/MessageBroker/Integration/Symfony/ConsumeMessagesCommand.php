<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\Symfony;

use Gaming\Common\MessageBroker\MessageBroker;
use Gaming\Common\MessageBroker\Model\Consumer\Consumer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Traversable;

final class ConsumeMessagesCommand extends Command
{
    /**
     * @param Traversable<string, Consumer> $consumers
     */
    public function __construct(
        private readonly MessageBroker $messageBroker,
        private readonly iterable $consumers
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'consumer',
                'c',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'List of consumers.'
            )
            ->addOption(
                'select-all-consumers',
                null,
                InputOption::VALUE_NONE,
                'Overwrites individual consumers. This is useful for the development environment.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $selectedConsumerNames = $this->selectedConsumerNames($input);
        if (count($selectedConsumerNames) === 0) {
            $output->writeln(
                sprintf(
                    'Please select one of the following consumers:%s* %s',
                    PHP_EOL,
                    implode(PHP_EOL . '* ', $this->availableConsumerNames())
                )
            );
            return Command::FAILURE;
        }

        $unknownConsumerNames = $this->unknownConsumerNames($input);
        if (count($unknownConsumerNames) !== 0) {
            $output->writeln(
                sprintf(
                    'The following consumers are unknown:%s* %s',
                    PHP_EOL,
                    implode(PHP_EOL . '* ', $unknownConsumerNames)
                )
            );
            return Command::FAILURE;
        }

        $this->messageBroker->consume(
            $this->createConsumers($input, $output)
        );

        return Command::SUCCESS;
    }

    /**
     * @return string[]
     */
    private function selectedConsumerNames(InputInterface $input): array
    {
        return $input->getOption('select-all-consumers') ?
            $this->availableConsumerNames() :
            $input->getOption('consumer');
    }

    /**
     * @return string[]
     */
    private function availableConsumerNames(): array
    {
        return array_keys(iterator_to_array($this->consumers));
    }

    /**
     * @return string[]
     */
    private function unknownConsumerNames(InputInterface $input): array
    {
        return array_keys(
            array_diff_key(
                array_flip($this->selectedConsumerNames($input)),
                iterator_to_array($this->consumers)
            )
        );
    }

    /**
     * @return iterable<Consumer>
     */
    private function createConsumers(InputInterface $input, OutputInterface $output): iterable
    {
        $consumers = array_intersect_key(
            iterator_to_array($this->consumers),
            array_flip($this->selectedConsumerNames($input))
        );

        if ($output->isVerbose()) {
            $consumers = array_map(
                static fn(Consumer $consumer) => new SymfonyConsoleConsumer($consumer, $output),
                $consumers
            );
        }

        return $consumers;
    }
}
