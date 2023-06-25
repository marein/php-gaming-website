<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\Symfony;

use Gaming\Common\MessageBroker\Consumer;
use Gaming\Common\MessageBroker\Integration\ForkPool\ForkPoolConsumer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Service\ServiceProviderInterface;

final class ConsumeMessagesCommand extends Command
{
    /**
     * @param ServiceProviderInterface<Consumer> $consumers
     */
    public function __construct(
        private readonly ServiceProviderInterface $consumers,
        private readonly string $allConsumersName = 'all'
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'consumer',
                InputArgument::REQUIRED,
                'Name of the consumer.'
            )
            ->addOption(
                'parallelism',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Defines how many messages can be processed in parallel.
                <comment>Not used by all implementations.</comment>'
            )
            ->addOption(
                'replicas',
                'r',
                InputOption::VALUE_OPTIONAL,
                'Defines how many processes will be started with the given configuration.
                This can be useful for sharing resources between processes.
                This can also be useful if the deployment environment does not provide a way to create replicas,
                e.g. in some development environments.
                <comment>Please use with caution.</comment>'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $consumerName = (string)$input->getArgument('consumer');
        $parallelism = max(1, (int)$input->getOption('parallelism'));
        $replicas = max(1, (int)$input->getOption('replicas'));

        if (!$this->consumers->has($consumerName) && $consumerName !== $this->allConsumersName) {
            $symfonyStyle->error(
                sprintf(
                    "Consumer doesn't exist. Available consumers:\n* %s (should only be used during dev)\n* %s",
                    $this->allConsumersName,
                    implode("\n* ", array_keys($this->consumers->getProvidedServices()))
                )
            );
            return Command::FAILURE;
        }

        $consumer = $this->replicateConsumerIfNeeded($this->getConsumerByName($consumerName), $replicas);

        pcntl_async_signals(true);
        pcntl_signal(SIGINT, $consumer->stop(...));
        pcntl_signal(SIGTERM, $consumer->stop(...));

        $symfonyStyle->success(
            sprintf(
                'Start consumer "%s" %s time/s with parallelism of %s.',
                $consumerName,
                $replicas,
                $parallelism
            )
        );

        $consumer->start($parallelism);

        return Command::SUCCESS;
    }

    private function getConsumerByName(string $consumerName): Consumer
    {
        return $consumerName === $this->allConsumersName
            ? new ForkPoolConsumer(
                array_map(
                    fn(string $consumerName): Consumer => $this->consumers->get($consumerName),
                    array_keys($this->consumers->getProvidedServices())
                )
            )
            : $this->consumers->get($consumerName);
    }

    private function replicateConsumerIfNeeded(Consumer $consumer, int $replicas): Consumer
    {
        return $replicas <= 1 ? $consumer : new ForkPoolConsumer(array_fill(0, $replicas, $consumer));
    }
}
