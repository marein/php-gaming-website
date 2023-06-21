<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\Symfony;

use Gaming\Common\MessageBroker\Consumer;
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
        private readonly ServiceProviderInterface $consumers
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
                'Defines how many messages can be processed in parallel. Not used by all implementations.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $parallelism = max(1, (int)$input->getOption('parallelism'));
        $consumer = $this->getConsumerByName($consumerName = $input->getArgument('consumer'));
        if ($consumer === null) {
            $symfonyStyle->error(
                sprintf(
                    "Consumer doesn't exist. Available consumers:\n* all (should only be used during dev)\n* %s",
                    implode("\n* ", array_keys($this->consumers->getProvidedServices()))
                )
            );
            return Command::FAILURE;
        }

        pcntl_async_signals(true);
        pcntl_signal(SIGINT, $consumer->stop(...));
        pcntl_signal(SIGTERM, $consumer->stop(...));

        $symfonyStyle->success('Start consumer "' . $consumerName . '".');

        $consumer->start($parallelism);

        return Command::SUCCESS;
    }

    private function getConsumerByName(string $consumerName): ?Consumer
    {
        if ($consumerName === 'all') {
            return new ForkPoolConsumer(
                array_map(
                    fn (string $consumerName): Consumer => $this->consumers->get($consumerName),
                    array_keys($this->consumers->getProvidedServices())
                )
            );
        }

        return $this->consumers->has($consumerName) ? $this->consumers->get($consumerName) : null;
    }
}
