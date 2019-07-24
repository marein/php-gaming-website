<?php
declare(strict_types=1);

namespace Gaming\Chat\Presentation\Console;

use Gaming\Chat\Application\ChatService;
use Gaming\Chat\Infrastructure\Messaging\CommandConsumer;
use Gaming\Common\MessageBroker\MessageBroker;
use Gaming\Common\Port\Adapter\Messaging\SymfonyConsoleConsumer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RabbitMqCommandListenerCommand extends Command
{
    /**
     * @var MessageBroker
     */
    private $messageBroker;

    /**
     * @var ChatService
     */
    private $chatService;

    /**
     * RabbitMqCommandListenerCommand constructor.
     *
     * @param MessageBroker $messageBroker
     * @param ChatService   $chatService
     */
    public function __construct(MessageBroker $messageBroker, ChatService $chatService)
    {
        parent::__construct();

        $this->messageBroker = $messageBroker;
        $this->chatService = $chatService;
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this
            ->setName('chat:command-listener');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->messageBroker->consume(
            new SymfonyConsoleConsumer(
                new CommandConsumer(
                    $this->chatService
                ),
                $output
            )
        );
    }
}
