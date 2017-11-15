<?php

namespace Gambling\Chat\Console;

use Gambling\Chat\Model\ChatGateway;
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
     * @var ChatGateway
     */
    private $chatGateway;

    /**
     * RabbitMqCommandListenerCommand constructor.
     *
     * @param MessageBroker $messageBroker
     * @param ChatGateway   $chatGateway
     */
    public function __construct(MessageBroker $messageBroker, ChatGateway $chatGateway)
    {
        parent::__construct();

        $this->messageBroker = $messageBroker;
        $this->chatGateway = $chatGateway;
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
                $this->chatGateway->initiateChat(
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
