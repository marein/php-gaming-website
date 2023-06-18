<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\Symfony;

use Gaming\Common\ForkPool\Channel\NullChannelPairFactory;
use Gaming\Common\ForkPool\ForkPool;
use Gaming\Common\MessageBroker\Consumer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * @template T
 */
final class ConsumeMessagesCommand extends Command
{
    /**
     * @param Consumer<T> $consumer
     * @param ServiceProviderInterface<T> $topicConsumers
     */
    public function __construct(
        private readonly Consumer $consumer,
        private readonly ServiceProviderInterface $topicConsumers
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
                'List of topic consumers.'
            )
            ->addOption(
                'select-all-consumers',
                null,
                InputOption::VALUE_NONE,
                'Overwrites individual topic consumers. This is useful for the development environment.'
            )
            ->addOption(
                'fork',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Number of forks. This is useful for sharing memory between processes.',
                1
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $selectedConsumerNames = $this->selectedConsumerNames($input);
        if (count($selectedConsumerNames) === 0) {
            $output->writeln(
                sprintf(
                    'Please select one of the following topic consumers:%s* %s',
                    PHP_EOL,
                    implode(PHP_EOL . '* ', $this->availableTopicConsumerNames())
                )
            );
            return Command::FAILURE;
        }

        $unknownConsumerNames = $this->unknownTopicConsumerNames($input);
        if (count($unknownConsumerNames) !== 0) {
            $output->writeln(
                sprintf(
                    'The following topic consumers are unknown:%s* %s',
                    PHP_EOL,
                    implode(PHP_EOL . '* ', $unknownConsumerNames)
                )
            );
            return Command::FAILURE;
        }

        $forkPool = new ForkPool(new NullChannelPairFactory());

        for ($i = 0, $numberOfWorkers = max(1, (int)$input->getOption('fork')); $i < $numberOfWorkers; $i++) {
            $forkPool->fork(
                new ConsumerTask($this->consumer, $this->filterSelectedTopicConsumers($input))
            );
        }

        $forkPool->signal()
            ->enableAsyncDispatch()
            ->forwardSignalAndWait([SIGINT, SIGTERM]);

        $forkPool->wait()
            ->killAllWhenAnyExits(SIGTERM);

        return Command::SUCCESS;
    }

    /**
     * @return string[]
     */
    private function selectedConsumerNames(InputInterface $input): array
    {
        return $input->getOption('select-all-consumers') ?
            $this->availableTopicConsumerNames() :
            $input->getOption('consumer');
    }

    /**
     * @return string[]
     */
    private function availableTopicConsumerNames(): array
    {
        return array_keys($this->topicConsumers->getProvidedServices());
    }

    /**
     * @return string[]
     */
    private function unknownTopicConsumerNames(InputInterface $input): array
    {
        return array_keys(
            array_diff_key(
                array_flip($this->selectedConsumerNames($input)),
                $this->topicConsumers->getProvidedServices()
            )
        );
    }

    /**
     * @return T[]
     */
    private function filterSelectedTopicConsumers(InputInterface $input): array
    {
        return array_map(
            fn(string $topicConsumerName) => $this->topicConsumers->get($topicConsumerName),
            $this->selectedConsumerNames($input)
        );
    }
}
