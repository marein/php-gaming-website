<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\Integration;

use Gaming\Common\MessageBroker\Integration\AmqpLib\AmqpConsumer;
use Gaming\Common\MessageBroker\Integration\AmqpLib\QueueConsumer\CompositeQueueConsumer;
use Gaming\Common\MessageBroker\Integration\AmqpLib\QueueConsumer\QueueConsumer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Traversable;

final class ConsumeMessagesCommand extends Command
{
    /**
     * @param Traversable<string, QueueConsumer> $queueConsumers
     */
    public function __construct(
        private readonly AmqpConsumer $amqpConsumer,
        private readonly iterable $queueConsumers
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
                    implode(PHP_EOL . '* ', $this->availableQueueConsumerNames())
                )
            );
            return Command::FAILURE;
        }

        $unknownConsumerNames = $this->unknownQueueConsumerNames($input);
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

        pcntl_async_signals(true);
        pcntl_signal(SIGINT, $this->amqpConsumer->stop(...));
        pcntl_signal(SIGTERM, $this->amqpConsumer->stop(...));

        $this->amqpConsumer->start(
            $this->createQueueConsumer($input, $output)
        );

        return Command::SUCCESS;
    }

    /**
     * @return string[]
     */
    private function selectedConsumerNames(InputInterface $input): array
    {
        return $input->getOption('select-all-consumers') ?
            $this->availableQueueConsumerNames() :
            $input->getOption('consumer');
    }

    /**
     * @return string[]
     */
    private function availableQueueConsumerNames(): array
    {
        return array_keys(iterator_to_array($this->queueConsumers));
    }

    /**
     * @return string[]
     */
    private function unknownQueueConsumerNames(InputInterface $input): array
    {
        return array_keys(
            array_diff_key(
                array_flip($this->selectedConsumerNames($input)),
                iterator_to_array($this->queueConsumers)
            )
        );
    }

    private function createQueueConsumer(InputInterface $input, OutputInterface $output): QueueConsumer
    {
        return new CompositeQueueConsumer(
            array_intersect_key(
                iterator_to_array($this->queueConsumers),
                array_flip($this->selectedConsumerNames($input))
            )
        );
    }
}
