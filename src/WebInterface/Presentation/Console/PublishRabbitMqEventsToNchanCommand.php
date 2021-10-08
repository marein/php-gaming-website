<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Console;

use Gaming\Common\MessageBroker\MessageBroker;
use Gaming\Common\Port\Adapter\Messaging\SymfonyConsoleConsumer;
use Gaming\WebInterface\Application\BrowserNotifier;
use Gaming\WebInterface\Infrastructure\Messaging\PublishRabbitMqEventsToNchanConsumer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class PublishRabbitMqEventsToNchanCommand extends Command
{
    /**
     * @var MessageBroker
     */
    private MessageBroker $messageBroker;

    /**
     * @var BrowserNotifier
     */
    private BrowserNotifier $browserNotifier;

    /**
     * PublishRabbitMqEventsToNchanCommand constructor.
     *
     * @param MessageBroker $messageBroker
     * @param BrowserNotifier $browserNotifier
     */
    public function __construct(MessageBroker $messageBroker, BrowserNotifier $browserNotifier)
    {
        parent::__construct();

        $this->messageBroker = $messageBroker;
        $this->browserNotifier = $browserNotifier;
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this
            ->setName('web-interface:publish-rabbit-mq-events-to-nchan');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->messageBroker->consume(
            new SymfonyConsoleConsumer(
                new PublishRabbitMqEventsToNchanConsumer(
                    $this->browserNotifier
                ),
                $output
            )
        );
    }
}
