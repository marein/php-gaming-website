<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Symfony;

use Gaming\Common\MessageBroker\MessageBroker;
use Gaming\Common\MessageBroker\Model\Consumer\Consumer;
use Gaming\Common\Port\Adapter\Messaging\SymfonyConsoleConsumer;
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
        $availableConsumers = iterator_to_array($this->consumers);

        $selectedConsumerNames = $input->getOption('consumer');
        if ($input->getOption('select-all-consumers')) {
            $selectedConsumerNames = array_keys($availableConsumers);
        }

        $consumers = [];
        foreach ($selectedConsumerNames as $selectedConsumerName) {
            if (!array_key_exists($selectedConsumerName, $availableConsumers)) {
                $output->writeln('Consumer "' . $selectedConsumerName . '" not known.');
                return Command::FAILURE;
            }
            $consumers[] = new SymfonyConsoleConsumer(
                $availableConsumers[$selectedConsumerName],
                $output
            );
        }

        if (count($consumers) === 0) {
            $output->writeln('No consumer selected.');
            return Command::FAILURE;
        }

        $this->messageBroker->consume($consumers);

        return Command::SUCCESS;
    }
}
