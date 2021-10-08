<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Console;

use Gaming\Common\Bus\Bus;
use Gaming\Common\MessageBroker\MessageBroker;
use Gaming\Common\Port\Adapter\Messaging\SymfonyConsoleConsumer;
use Gaming\ConnectFour\Port\Adapter\Messaging\RefereeConsumer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RefereeCommand extends Command
{
    /**
     * @var MessageBroker
     */
    private MessageBroker $messageBroker;

    /**
     * @var Bus
     */
    private Bus $commandBus;

    /**
     * RefereeCommand constructor.
     *
     * @param MessageBroker $messageBroker
     * @param Bus           $commandBus
     */
    public function __construct(MessageBroker $messageBroker, Bus $commandBus)
    {
        parent::__construct();

        $this->messageBroker = $messageBroker;
        $this->commandBus = $commandBus;
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this
            ->setName('connect-four:referee');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->messageBroker->consume(
            new SymfonyConsoleConsumer(
                new RefereeConsumer(
                    $this->commandBus,
                    $this->messageBroker
                ),
                $output
            )
        );
    }
}
