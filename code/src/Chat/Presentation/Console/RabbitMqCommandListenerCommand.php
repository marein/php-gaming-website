<?php

namespace Gambling\Chat\Presentation\Console;

use Gambling\Chat\Application\ChatService;
use Gambling\Common\Port\Adapter\Messaging\MessageBroker;
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
    protected function configure()
    {
        $this
            ->setName('chat:command-listener');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $commandToMethod = [
            'chat.initiate-chat' => function (array $payload) {
                $this->chatService->initiateChat(
                    $payload['ownerId'],
                    $payload['authors']
                );
            }
        ];

        $this->messageBroker->consume(
            'chat.command-listener',
            array_keys($commandToMethod),
            function (string $body, string $routingKey) use ($commandToMethod) {
                $method = $commandToMethod[$routingKey] ?? null;
                $payload = json_decode($body, true);

                $method($payload);
            }
        );
    }
}
