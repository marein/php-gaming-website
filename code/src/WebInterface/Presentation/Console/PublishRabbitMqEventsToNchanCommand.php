<?php

namespace Gambling\WebInterface\Presentation\Console;

use Gambling\Common\MessageBroker\MessageBroker;
use Gambling\WebInterface\Application\BrowserNotifier;
use Gambling\WebInterface\Infrastructure\Messaging\PublishRabbitMqEventsToNchanConsumer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class PublishRabbitMqEventsToNchanCommand extends Command
{
    /**
     * @var MessageBroker
     */
    private $messageBroker;

    /**
     * @var BrowserNotifier
     */
    private $browserNotifier;

    /**
     * PublishRabbitMqEventsToNchanCommand constructor.
     *
     * @param MessageBroker   $messageBroker
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
    protected function configure()
    {
        $this
            ->setName('web-interface:publish-rabbit-mq-events-to-nchan');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->messageBroker->consume(
            new PublishRabbitMqEventsToNchanConsumer(
                $this->browserNotifier
            )
        );
    }
}
